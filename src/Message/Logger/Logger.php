<?php
namespace SwooleTar\Message\Logger;

/**
 * 系统日志管理类
 */
class Logger
{
    /**
     * 服务器启动日志信息
     */
    protected static $logs = [];

    /**
     * 设置启动日志
     */
    public static function info($index,$msg)
    {
        if(!empty($index)){
            self::$logs[$index] = $msg;
        }
        return (new static);
    }

    /**
     * 获取服务启动日志
     */
    public function getLoggers()
    {
        return self::$logs;
    }
}