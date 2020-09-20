<?php
/*
 * @Description: 
 * @Version: 2.0
 * @Autor: gang
 * @Date: 2020-08-31 23:32:54
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2020-09-09 22:14:26
 */

namespace SwooleTar\Server;

use Swoole\Server as SwooleServer;
use SwooleTar\Foundation\Application;
use SwooleTar\Supper\Inotify;


abstract class Server
{
    /**
     * Server|HttpServer|WebSocketServer|
     */
    protected $swooleServerobj;

    /**
     * redis 连接对象
     */
    protected $redis = null;
    
    /**
     * 监听端口
     */
    protected $port = 9800;

    /**
     * 服务IP
     */
    protected $host = '0.0.0.0';

    /**
     * 是否开启热重启监听
     *
     * @var boolean
     */
    protected $watchFile = false;


    /**
     * pid
     */
    protected $pidFile = "/runtime/SwooleTar.pid";

    /**
     * 应用对象
     */
    protected $app ;

    /**
     * @description: 记录pid信息
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    protected $pidMap = [
        'masterPid' => 0,
        'managerPid' => 0,
        'workerPid' => [],
        'taskPid' => [],
    ];

    /**
     * @description: 注册的回调事件 
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    protected $event = [
            #这是所有服务均会注册的时间
            'server' => [
                'start'         => 'onStart',
                'ManagerStart'  => 'onManagerStart',
                'Shutdown'      => 'onShutdown',
                'WorkerStart'   => 'onWorkerStart',
                'workerStop'    => 'onWorkerStop',
                'workerError'   => 'onWorkerError',
            ],
            #子类的服务
            'sub' => [],
            #额外扩展的回调函数(onStart)
            'ext' => [],
    ];

    /**
     * @description: swoole的相关配置信息 
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    protected $config = [
        'task_worker_num' => 0,
    ];

    /**
     * 创建服务
     */
    protected abstract function createServer();


    // protected abstract function initSetting();
    /**
     * 子服务监听的事件扩展
     */
    protected abstract function initEvent();

    /**
     * 初始化并注册服务
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * 设置swoole的回调事件
     */
    protected function setSwooleEvent()
    {
        foreach($this->event as $type => $events){ 
            foreach($events as $event => $func){
               $this->swooleServerobj->on($event,[$this,$func]);
            }
        }
    }
    
    /**
     * 初始化服务
     */
    public function start()
    {
        //1、创建 swoole server
        $this->createServer();
        
        //2、设置swoole配置
        $this->swooleServerobj->set($this->config);

        //3、设置需要注册的回调函数
        $this->initEvent();

        //开启rpc服务
        $rpcConfig = $this->app->make('config');
        // debug($rpcConfig);
        $tcpable = $rpcConfig->get('swoole.http.tcpable');
        // debug($tcpable);
        if($tcpable){
            new \SwooleTar\Rpc\Rpc($this->swooleServerobj,$this->app);            
        }

        //4、设置swoole的回调函数
        $this->setSwooleEvent();
        
        // 5. 启动
        $this->swooleServerobj->start();
    }
    
    /**
     * 设置master 进程、manager 进程id
     */
    public function onStart($server)
    {
        $Config = $this->app->make('config');
        if($Config->get('swoole.debug')){
            app('Logs')::info('master_pid ',$server->master_pid);
            app('Logs')::info('manager_pid',$server->manager_pid);
            app('Logs')::info('Rpc Server ',join(':',[$Config->get('swoole.http.rpc.host'),$Config->get('swoole.http.rpc.port')]));
            //打印启动日志
            $this->info_logs();
            // 通过注册事件触发(路由注册)
            $this->app->make('event')->trigger('start');

        }
        
        

        // 记录服务进程id
        $this->pidMap['masterPid'] = $server->master_pid;
        $this->pidMap['managerPid'] = $server->manager_pid;

        //主进程启动前调用
        if ($this->watchFile) {
            $this->inotify = new Inotify($this->app->getBasePath(), $this->watchEvent());
            $this->inotify->start();
        }
        // debug(app('Logs')::getLoggers());
    }

    /**
     * @description: 
     * @param {type} 
     * @return {type} 
     */
    public function onManagerStart($server)
    {

    }

    /**
     * 
     */
    public function onShutdown($server)
    {
        
    }

    /**
     * @description: 此事件在 Worker 进程 / Task 进程启动时发生，这里创建的对象可以在进程生命周期内使用。
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {
        // if($server->taskworker){
        //     debug("此进程{$workerId}为worker进程");
        // }else{
        //     debug("此进程{$workerId}为task进程");
        // }
        $this->redis = new \Redis();
        $this->redis->pconnect("127.0.0.1", 6379);
        $this->redis->auth('phpstudy');
    }


    /**
     * 
     */
    public function onWorkerStop()
    {

    }

    /**
     * 
     */
    public function onWorkerError()
    {

    }

    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param array
     *
     * @return static
     */
    public function setEvent($type, $event)
    {
        // 暂时不支持直接设置系统的回调事件
        if ($type == "server") {
            return $this;
        }
        $this->event[$type] = $event;
        return $this;
    }

    /**
     * 文件热加载监听事件
     */
    protected function watchEvent()
    {
        return function($event){
            $action = 'file:';
            switch ($event['mask']) {
                case "IN_CREATE":
                  $action = 'IN_CREATE';
                  break;
                case "IN_DELETE":
                  $action = 'IN_DELETE';
                  break;
                case "IN_MODIFY":
                  $action = 'IN_MODIF';
                  break;
                case "IN_MOVE":
                  $action = 'IN_MOVE';
                  break;
            }
            $this->swooleServerobj->reload();
        };
    }

    /**
     * 获取配置信息
     *
     * @return array
     */
    public function getConfig():array
    {
        return $this->config;
    }

    /**
     * 设置配置信息
     *
     * @return array
     */
    public function setConfig($config)
    {
        $this->config = array_map($this->config , $config);
        return $this;
    }

    /**
     * 服务启动日志
     */
    public function info_logs()
    {
        $start_logs = $this->app->make('Logs')->getLoggers();
        // debug($start_logs);
        $info = "---------------------------------------------------------------\n";
        $start_logs[''] = "---------------------------------------------------------------\n";
        // debug($start_logs);
        foreach($start_logs as $key => $value){
            if(!empty($key)){
                $info.= join("                ", [$key,$value."\n"]);
            }else{
                $info.= join("", [$key,$value."\n"]);
            }
            
        }  
        echo $info;
    }
}