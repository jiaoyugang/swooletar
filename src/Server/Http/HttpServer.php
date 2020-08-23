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
    //    debug([$httpConfig->getConfig('swoole.http.host'),
    //    $httpConfig->getConfig('swoole.http.port')]);
        $this->swooleServer = new SwooleHttpServer(
            $httpConfig->getConfig('swoole.http.host'),
            $httpConfig->getConfig('swoole.http.port')
        );
        $this->watchFile = false;
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

        $return = app('route');

        // debug($return);
        
         // 执行控制器的方法
         $return = app('route')->setMethod($httpRequest->getMethod())->match($httpRequest->getUriPath());
         
         $response->header("content-type","text/html;charset=utf-8");
         $response->end($return);
        // debug($httpRequest->getMethod());
        #发送响应"<h1>hello swooleTar</h1>"
        // $response->header("content-type","text/html;charset=utf-8");
        // $response->end(json_encode(['name' => '测试' ,'title' => 'swoole'] ,JSON_UNESCAPED_UNICODE));
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