<?php
/*
 * @Description: 
 * @version: 
 * @Author: 1355528968@qq.com
 * @Date: 2020-08-23 22:22:14
 */
namespace SwooleTar\Server\WebSocket;

use SwooleTar\Route\Route;
use SwooleTar\Server\Http\HttpServer;
use Swoole\WebSocket\Frame as wsFrame;
use Swoole\Http\Request as httpRequest;
use Swoole\WebSocket\Server as wsServer;
use Swoole\Http\Response as httpResponse;

class WebSocketServer extends HttpServer
{
    /**
     * 创建服务
     */
    public function createServer()
    {
        $httpConfig = app('config');
        $host = $httpConfig->get('swoole.wsocket.host');
        $port = $httpConfig->get('swoole.wsocket.port');
        app('Logs')::info('Http Server','http://'.$host.':'.$port);
        app('Logs')::info('WServer    ','ws://'.$host.':'.$port);
        $this->swooleServerobj = new wsServer($host,$port);
    }

    
    /**
     * 初始化监听的事件
     */
    public function initEvent()
    {
        $event = [
            'request'   =>  'onRequest',
            'open'      =>  'onOpen',
            'message'   =>  'onMessage',
            'close'     =>  'onClose',
        ];
        
        //判断是否开启自定握手事件
        if( $this->app->make('config')->get('swoole.wsocket.is_handshake') ){
            $event["handshake"] = 'onHandshake'; //自定义握手事件
        };
        $this->setEvent('sub',$event);
    }

    /**
     * @description: WebSocket 建立连接后手动进行握手
     * @param {type} 
     * @return {type} 
     */
    public function onHandshake(httpRequest $request, httpResponse $response)
    {
        //到事件中心解析监听器
        $this->app->make('event')->trigger('handshake',[$this,$request,$response]);
        // 设置手动握手事件后，onOpen事件不会再次触发
        $this->onOpen($this->swooleServerobj,$request);


        // 记录用户登录信息
        // Connection::init($request->fd,$request->server['path_info']); //记录客户端请求信息
    }

    /**
     * @description: 
     * @param httpRequest $request 是一个http请求对象，包含了客户端发来的握手请求信息
     * @return {type} 
     */
    public function onOpen(wsServer $server, httpRequest $request)
    {
        debug('onOpen');
        // 1、获取访问的地址
        $path = $this->getPath($request->fd);
        app('route')->setFlag('Websocket')->setMethod('open')->match($path ,[$server,$request]);
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
        //
        $this->app->make('event')->trigger('wsmessagefront',[$this,$server,$frame]);
        // 从redis获取用户认证路径
        // $redis = $this->getRedis();
        // $key = $this->app->make('config')->get('swoole.route.jwt.key');
        // $path_info = json_decode($redis->hget($key,$frame->fd),true);
        $path = $this->getPath($frame->fd);
        app('route')->setFlag('Websocket')->setMethod('message')->match($path,[$server,$frame]);
    }

    /**
     * 断开连接
     */
    public function onClose(wsServer $server , int $fd)
    {
        // $path_info = Connection::get($fd);
        # 触发监听客户端下线事件
        $redis = $this->getRedis();
        $this->app->make('event')->trigger('wsclose',[$this,$server,$redis,$fd]);

        // $path = $this->getPath($fd);
        // app('route')->setFlag('Websocket')->setMethod('close')->match($path,[$server,$fd]);
        // #Connection::del($fd);
        // $redis = $this->getRedis();
        // $key = $this->app->make('config')->get('swoole.route.jwt.key');
        // $redis->hdel($key,$fd);
    }

    /**
     * 获取WebSocket用户请求认证路径
     */
    public function getPath($fd)
    {
         // 从redis获取用户认证路径
         $redis = $this->getRedis();
         $key = $this->app->make('config')->get('swoole.route.jwt.key');
         $path_info = json_decode($redis->hget($key,$fd),true);
         return $path_info['path'];
    }
}
