<?php
namespace SwooleTar\Rpc;

use Swoole\Server as SwooleServer;
use Swoole\StringObject;

class Rpc
{

    protected $host;
    
    protected $port;

    protected $type;

    /**
     * 初始化Rpc服务
     */
    public function __construct(SwooleServer $server,$rpcConfig)
    {   
        $this->host = $rpcConfig->getConfig('swoole.rpc.host');
        $this->port = $rpcConfig->getConfig('swoole.rpc.port');
        $this->type = $rpcConfig->getConfig('swoole.rpc.type');
        $this->initRpc($server);
    }

    public function initRpc($server)
    {
        $tcp_server = $server->listen($this->host,$this->port,SWOOLE_SOCK_TCP);
        $tcp_server->set([
            'worker_num' => 1
        ]);
        $tcp_server->on('connect', [$this, 'onConnect']);
        $tcp_server->on('receive', [$this, 'onReceive']);
        $tcp_server->on('close', [$this, 'onClose']);
    }

    /**
     *   监听连接
     */
    public function onConnect(SwooleServer $server,int $fd)
    {
        debug('服务监听端口已开启');
    }

    /**
     * 接收数据
     */
    public function onReceive( SwooleServer $server, int $fd, $threadId, $data)
    {
        $server->send($fd, 'Swoole: '.$data);
        $server->close($fd);
    }


    /**
     * 关闭连接
     */
    public function onClose()
    {
        debug('服务关闭');
    }
}