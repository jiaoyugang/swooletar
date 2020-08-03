<?php
namespace SwooleTar\Foundation;

use SwooleTar\Container\Container;
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
        if (!empty($path)) {
            $this->setBasePath($path);
        }
        $this->registerBaseBindings();
        echo self::SWOSTAR_WELCOME.PHP_EOL;
    }

    /**
     * 启动服务
     */
    public function run()
    {
        (new HttpServer($this))->start();
    }

    /**
     * 绑定示例对象到容器
     */
    public function registerBaseBindings()
    {
        // debug((new \SwooleTar\Index())->index());
        $binds= [
            //标示、对象
            'Index' => (new \SwooleTar\Index()),
        ];

        // debug($binds);
        foreach($binds as $key => $value){
            // var_dump($key,$value);
            $this->bind($key,$value);
        }


    }

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