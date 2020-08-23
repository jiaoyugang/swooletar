<?php
namespace SwooleTar\Message\Http;

use Swoole\Http\Request as HttpRequest;

class Request
{
    protected $method;

    protected $uriPath;

    /**
     * 获取请求方法
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 获取请求uri路径
     */
    public function getUriPath()
    {
        return $this->uriPath;
    }
    
    /**
     * 解析http请求头信息
     */
    public static function initHttpRequest(HttpRequest $request)
    {   
        //从容器中获取httpRequest对象
        $current_class_object = app('httpRequest');
        
        $current_class_object->method = $request->server['request_method'];

        $current_class_object->uriPath = $request->server['request_uri'];
        
        //返回值为当前类的实例对象
        return $current_class_object;
    }
}