<?php
namespace SwooleTar\Foundation;

use SwooleTar\Container\Container;
use SwooleTar\Route\Route;
use SwooleTar\Server\Http\HttpServer;


class Application extends Container
{
    protected const SWOSTAR_WELCOME = "
    _____                     _____     ___
    /  __/             ____   /  __/  __/  /__   ___ __    __  __
    \__ \  | | /| / / / __ \  \__ \  /_   ___/  /  _`  |  |  \/ /
    __/ /  | |/ |/ / / /_/ /  __/ /   /  /_     |  (_|  |  |   _/
    /___/   |__/\__/  \____/  /___/    \___/     \___/\_|  |__|
    ";

    /**
     * 监听根目录，接收变量
     */
    protected $basePath = "";

    /**
     * 初始化服务
     */
    public function __construct($path = null)
    {
        // debug('获取对象实例');
        if (!empty($path)) {
            $this->setBasePath($path);
        }
        echo self::SWOSTAR_WELCOME.PHP_EOL;
        
        //初始化Container容器:所有服务启动时，默认绑定到容器中
        $this->registerBaseBindings();

        // debug(app('Config'));
        //2、初始化路由
        // $this->initRoute();
    }

    /**
     * 服务启动方法
     */
    public function run()
    {
        (new HttpServer($this))->start();
    }

    /**
     * 绑定实例到容器
     */
    public function registerBaseBindings()
    {
        //实例化Application对象给Container
        self::setInstance($this);

        //系统服务启动时，默认绑定实例对象
        $binds= [
            //标示=>对象
            'Config' => (new \SwooleTar\Config\Config()), //配置文件
            'Index' => (new \SwooleTar\Index()), //测试文件
            'HttpRequest' => (new \SwooleTar\Message\Http\Request()), //http请求服务组件
            'Route' => Route::getInstance()->registerRouter(),  //框架路由
        ];
        foreach($binds as $key => $value){
            $this->bind($key,$value);
        }
    }
    
    /**
     * 初始并加载化路由信息（此方法废弃，移到容器批量注册registerBaseBindings()）
     */
    public function initRoute()
    {
        $this->bind('Route',  Route::getInstance()->registerRouter());
        // debug('路由初始化');
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