<?php
namespace SwooleTar\Server\Http;

use SwooleTar\Server\Server;
use SwooleTar\Message\Http\Request as HttpRequest;

use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;

class HttpServer extends Server
{
    /**
     * 创建服务
     */
    public function createServer()
    {
        $httpConfig = app('config');
        $this->swooleServerobj = new SwooleHttpServer($httpConfig->getConfig('swoole.http.host'),$httpConfig->getConfig('swoole.http.port'));
    }

    /**
     * 初始化事件
     */
    protected function initEvent()
    {
        $this->setEvent('sub',[
            'request'   => 'onRequest',
            'task'      => 'onTask',
            'finish'    => 'onFinish',
        ]); 
    }
    
    /**
     * @param SwooleRequest $request
     * @param SwooleResponse $reponse
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        //过滤Google favicon.ico请求
       
        if($request->server['request_uri'] == '/favicon.ico'){
            $response->status(404);
            $response->end();
            return null;
        }
        
        $httpRequest = HttpRequest::initHttpRequest($request);
        // $httpRequest = app('httpRequest');
        // debug($httpRequest->getMethod());
        // debug($httpRequest->getUriPath());

         // 执行控制器的方法
         $return = app('route')->setFlag('Http')->setMethod($httpRequest->getMethod())->match($httpRequest->getUriPath());
         
         $response->header("content-type","text/html;charset=utf-8");
         $response->end($return);
    }

    /**
     * 异步耗时任务
     * @param SwooleServer $server
     * @param int   $task_id
     * @param int   $src_worker_id
     * @param mixed $data
     * @return void
     */
    public function onTask(SwooleServer $server,int $task_id,int $src_worker_id,mixed $data)
    {
        // $server->task();
    }
    
    /**
     * 此函数发生下worker进程中
     *
     * @param SwooleServer $server
     * @param int $task_id
     * @param mixed $data
     * @return void
     */
    public function onFinish(SwooleServer $server , int $task_id , mixed $data)
    {

    }
}