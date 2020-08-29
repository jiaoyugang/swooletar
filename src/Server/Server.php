<?php
namespace SwooleTar\Server;

use SwooleTar\Foundation\Application;
use SwooleTar\Supper\Inotify;

/**
 * swoole基类
 */
abstract class Server
{
    /**
     * Server|HttpServer|WebSocketServer|
     */
    protected $swooleServerobj;

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
     * 记录pid信息
     */
    protected $pidMap = [
        'masterPid' => 0,
        'managerPid' => 0,
        'workerPid' => [],
        'taskPid' => [],
    ];

    /**
     * 注册的回调事件
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
     * swoole的相关配置信息
     */
    protected $config = [
        'task_worker_num' => 0,
    ];

    /**
     * 创建服务
     */
    protected abstract function createServer();


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

        // rpc服务
        $rpcConfig = app('config');
        $tcpable = $rpcConfig->getConfig('swoole.rpc.tcpable');
        if($tcpable){
            new \SwooleTar\Rpc\Rpc($this->swooleServerobj,$rpcConfig);
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
        $Config = app('config');
        
        if($Config->getConfig('swoole.debug')){
            // 打印服务配置信息
            echo "---------------------------------------------------------------\n";
            echo "host                  : {$this->host}\n";
            echo "port                  : {$this->port}\n";
            echo "master_pid            : {$server->master_pid}\n";
            echo "manager_pid           : {$server->manager_pid}\n";
            echo "------------------------------\n";

            $tcpable = $Config->getConfig('swoole.rpc.tcpable');
            if($tcpable){
            echo "Rpc Server status     : true\n";
            }else{
            echo "Rpc                   : false\n";
            }
            echo "Rpc server address    : ".$Config->getConfig('swoole.rpc.host').':'.$Config->getConfig('swoole.rpc.port')."\n";
        }
        

        // 记录服务进程id
        $this->pidMap['masterPid'] = $server->master_pid;
        $this->pidMap['managerPid'] = $server->manager_pid;

        //主进程启动前调用
        if ($this->watchFile) {
            $this->inotify = new Inotify($this->app->getBasePath(), $this->watchEvent());
            $this->inotify->start();
        }
    }

    /**
     * 
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
     * 
     */
    public function onWorkerStart()
    {
        
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

}