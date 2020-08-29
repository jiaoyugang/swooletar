<?php
namespace SwooleTar\Server\WebSocket;

use Swoole\WebSocket\Frame as wsFrame;
use Swoole\WebSocket\Server as wsServer;
use Swoole\Http\Request as httpRequest;
use SwooleTar\Route\Route;
use SwooleTar\Server\Http\HttpServer;

class WebSocketServer extends HttpServer
{
    /**
     * 创建服务
     */
    public function createServer()
    {
        $httpConfig = app('config');
        $this->swooleServerobj = new wsServer($httpConfig->getConfig('swoole.wsocket.host'),$httpConfig->getConfig('swoole.wsocket.port'));
    }


    /**
     * 初始化监听的事件
     */
    public function initEvent()
    {
        $this->setEvent('sub',[
            'request' => 'onRequest',
            'open' => 'onOpen',
            'message' => 'onMessage',
            'close'   => 'onClose',
        ]);
    }

    /**
     * @param httpRequest $request 是一个http请求对象，包含了客户端发来的握手请求信息
     * 
     */
    public function onOpen(wsServer $server, httpRequest $request)
    {
        // 1、获取访问的地址
        debug($request->server['path_info']);
        // debug([$request->server['request_method'],$request->server['path_info']]);
        Connection::init($request->fd,$request->server['path_info']); //记录客户端请求信息
        app('route')->setFlag('Websocket')->setMethod('open')->match($request->server['path_info'] ,[$server,$request]);
    }
    
    /**
     * 发送消息
     */
    public function onMessage(wsServer $server,wsFrame $frame)
    {
        if(empty($frame->data)){
            $server->push($frame->fd,'请输入你要发送的消息');
            return false;
        }
        $path_info = Connection::get($frame->fd);
        // debug($path_info);
        app('route')->setFlag('Websocket')->setMethod('message')->match($path_info['path'],[$server,$frame]);
    }

    /**
     * 断开连接
     */
    public function onClose(wsServer $server , int $fd)
    {
        $path_info = Connection::get($fd);
        $return = app('route')->setFlag('Websocket')->setMethod('close')->match($path_info['path'],[$server,$fd]);
        Connection::del($fd);

    }
}
