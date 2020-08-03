<?php
namespace SwooleTar\Route;

class Route
{
    protected $routes = [];

    protected $verbs = ['GET','POST','PUT','PATH','DELETE']; 

    public function __construct()
    {
        
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


    public function match()
    {

    }
    
    public function addRoute($methods , $uri ,$action)
    {
        foreach($methods as $method){
            $this->routes[$method] [$uri] = $action;
        }
        return $this;
    }
}