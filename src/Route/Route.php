<?php
namespace SwooleTar\Route;

class Route
{
    /**
     * 单例实例化对象
     */
    public static $instance = null;

    /**
     * 存储解析后路由
     */
    protected $routes = [];

    /**
     * 定义访问类型
     */
    protected $verbs = ['GET','POST','PUT','PATH','DELETE']; 

    /**
     * 记录路由的文件地址
     */
    protected $routeMap = [];

    /**
     * 服务类型
     */
    public $flag = '';

    /**
     * 请求方法
     */
    protected $method = null;
    
    
    /**
     * 加载注册路由文件
     */
    public function __construct()
    {
        if(app()->getBasePath()){
            $this->routeMap = [
                'Http' => app()->getBasePath().'/route/http.php',  //http server路由
                'Websocket' => app()->getBasePath().'/route/websocket.php',  //websocket server路由
            ];
        }else{
            debug(['SwooleTar\Route' => '路由文件不存在']);
        }
        
    }
    
    /**
     * Route单例
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance ;
    }

    public function get($uri,$action)
    {
        $this->addRoute(['get'],$uri,$action);
    }

    public function post($uri,$action)
    {
        $this->addRoute(['post'],$uri,$action);
    }

    public function put($uri,$action)
    {
        $this->addRoute(['put'],$uri,$action);
    }

    public function delete($uri,$action)
    {
        $this->addRoute(['delete'],$uri,$action);
    }

    public function any($uri,$action)
    {
        $this->addRoute(self::$verbs,$uri,$action);
    }

    /**
     * 注册WebSocket路由
     */
    public function wsController($uri,$controller)
    {
        $wsAction = [
            'open',
            'message',
            'close'
        ];
        foreach($wsAction as $key => $action){
            $this->addRoute([$action],$uri,$controller.'@'.$action);
        }
    }
    
    /**
     * 根据请求校验路由，并执行方法
     */
    public function match($request_uri,$params = [])
    {
        //1、先验证请求uri格式是否是"/"开头
        $uri = stristr($request_uri,"\/") == 0 ? $request_uri : "\/{$request_uri}"; 
        $method = strtolower($this->method);
        
        //2、遍历验证注册路由的uri和请求的uri是否匹配
        foreach($this->routes[$this->flag][$method] as $uri => $value){
            
            if ( strcasecmp($request_uri,$uri) == 0 ) {
                $action = $value;
                break;
            }
        }
        // 3、检测请求方法是否为空
        if (!empty($action)) {
            return $this->runAction($action,$params);
        }
        debug('请求方法未找到');
        return "404";
    }
    
    /**
     * 请求路由匹配成功后调用返回
     */
    protected function runAction($action,$params = [])
    {
        $namespace = "\App\\".$this->flag."\Controller\\";

        //1、验证路由是闭包还是字符串
        // 闭包直接返回
        if ($action instanceof \Closure) {
            return $action(...$params);
        } else {
            // 控制器解析
            // IndexController@dd
            $arr = \explode("@", $action);
            // $namespace.$arr[0];
            $controller = join('',[$namespace,$arr['0']]);
            
            $class = new $controller();
            //调用请求方法
            return $class->{$arr[1]}(...$params);
        }
    }


    /**
     * 绑定路由
     */
    public function addRoute($methods , $uri ,$action)
    {
        foreach($methods as $method){
            $this->routes[$this->flag][$method][$uri] = $action;
        }
        // debug($this->routes);
        return $this;
    }

    /**
     * 获取路由信息
     */
    public function getRoute()
    {
        return $this->routes;
    }


    /**
     * 家在路由信息
     */
    public function registerRouter()
    {
        foreach($this->routeMap as $key => $path){
            $this->flag = $key; //服务类型标示
            require_once $path;
        }
        return $this;
    }

    /**
     * 设置请求方法
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 设置服务类型
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
        return $this;
    }

    
}