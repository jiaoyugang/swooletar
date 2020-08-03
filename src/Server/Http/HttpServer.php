<?php
namespace SwooleTar\Server\Http;

use SwooleTar\Server\Server;
use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
{
    /**
     * 创建服务
     */
    public function createServer()
    {
        $this->swooleServer = new SwooleHttpServer($this->host,$this->port);
        $this->watchFile = true;
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
     * @param $request
     * @param $reponse
     */
    public function onRequest($request,$response)
    {
        //过滤Google favicon.ico请求
        
        // if(isset($request->server['request_uri']) && ($request->server['request_uri'] == '/favicon.ico')){
        //     $response->status(404); 
        //     $response->end();
        // }

        #发送响应"<h1>hello swooleTar</h1>"
        $response->header("content-type","text/html;charset=utf-8");
        $response->end(json_encode(['name' => '测试' ,'title' => 'swoole'] ,JSON_UNESCAPED_UNICODE));
    }

    /**
     * 异步耗时任务
     *
     * @return void
     */
    public function onTask($server,$task_id,$src_worker_id,$data)
    {
        // $server->task();
    }

    /**
     * 此函数发生下worker进程中
     *
     * @param [type] $server
     * @param [type] $task_id
     * @param [type] $data
     * @return void
     */
    public function onFinish($server , $task_id , $data)
    {

    }
}