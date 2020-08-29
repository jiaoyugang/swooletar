<?php
namespace SwooleTar\Server\WebSocket;

/**
 * WebSocket 客户端连接信息
 */
class Connection
{
    /**
     * 存储用户请求信息
     * fd =>  [
     *      'path' => xxx,
     *      'xxx' => ''
     *  ]
     */
    protected static $connection = [];

    /**
     * 记录用户请求信息
     */
    public static function init($fd,$path)
    {
        self::$connection[$fd]['path'] = $path;
    }

    /**
     * 获取用户请求信息
     */
    public static function get($fd)
    {
        return self::$connection[$fd];
    }

    /**
     * 客户端关闭连接，或断开连接移除连接信息
     */
    public static function del($fd)
    {
        unset(self::$connection[$fd]);
    }

}