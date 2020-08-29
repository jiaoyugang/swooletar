<?php
namespace SwooleTar\Foundation;

use SwooleTar\Container\Container;
use SwooleTar\Route\Route;
use SwooleTar\Server\Http\HttpServer;
use SwooleTar\Server\WebSocket\WebSocketServer;

class Application extends Container
{
    protected const SWOSTAR_WELCOME = "
    _____                     _____     ___
    /  __/             ____   /  __/  __/  /__   ___ __    __  __
    \__ \  | | /| / / / __ \  \__ \  /_   ___/  /  _`  |  |  \/ /
    __/ /  | |/ |/ / / /_/ /  __/ /   /  /_     |  (_|  |  |   _/
    /___/   |__/\__/  \____/  /___/    \___/     \___/\_|  |__|
    ";

    protected $serviceObj = null;

    /**
     * 监听根目录，接收变量
     */
    protected $basePath = "";

    /**
     * 初始化服务
     */
    public function __construct($path = null)
    {
        //实例化Application对象给Container
        self::setInstance($this);

        // debug('获取对象实例');
        if (!empty($path)) {
            $this->setBasePath($path);
        }
        echo self::SWOSTAR_WELCOME.PHP_EOL;
       
        //初始化Container容器://系统服务启动时，默认绑定实例对象到容器中
        $this->registerBaseBindings();
    }

    /**
     * 服务启动方法
     */
    public function run($argv = '')
    {
        if(!isset($argv[1]) || empty($argv[1])){
            exit("请正确输入服务启动命令\n");
        }
        switch($argv['1']){
            case 'http:start':
                (new HttpServer($this))->start();
                break;
            case 'ws:start':
                (new WebSocketServer($this))->start();
                break;
            default:
                exit("请正确输入服务启动命令\n");
                break;
        }
        
    }

    /**
     * 绑定实例到容器
     */
    public function registerBaseBindings()
    {
        $binds= [
            //标示=>对象
            'Config'        =>      (new \SwooleTar\Config\Config()), //配置文件
            'Index'         =>      (new \SwooleTar\Index()), //测试文件
            'HttpRequest'   =>      (new \SwooleTar\Message\Http\Request()), //http请求服务组件
            'Route'         =>      Route::getInstance()->registerRouter(),  //注册路由
        ];
        foreach($binds as $key => $value){
            $this->bind($key,$value);
        }
    }
    


    #---------------------------------- 接收项目根目录
    
    /**
     *  设置监听根目录
     */
    public function setBasePath($path)
    {
        $this->basePath = rtrim($path, '\/');
    }

    /**
     * 获取监听目录
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}