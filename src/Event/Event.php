<?php
/*
 * @Description: 
 * @Version: 2.0
 * @Autor: gang
 * @Date: 2020-08-31 22:55:48
 * @LastEditors: gang
 * @LastEditTime: 2020-09-03 15:39:08
 */
namespace SwooleTar\Event;

class Event
{
    protected $events = [];

    /**
     * @description: 注册事件
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    public function register($event,$callback)
    {
        $event = strtolower($event);
        $this->events[$event] = ['callback' => $callback];
    }

    /**
     * @description: 触发事件
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    public function trigger($event,$params = [])
    {
        $event = strtolower($event);
        if(isset($this->events[$event])){
            $this->events[$event]['callback'](...$params);
            return true;
        }
        debug('事件不存在');
    }

    /**
     * @description: 查看事件
     * @param array $event
     * @return mixed
     * @author: gang
     */
    public function getEvents($event)
    {
        // $event = strtolower($event);
        
        return empty($event) ? $this->events : $this->events[$event];
    }

}