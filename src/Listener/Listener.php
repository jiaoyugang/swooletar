<?php
/*
 * @Description: 事件监听类
 * @Version: 2.0
 * @Autor: gang
 * @Date: 2020-09-03 09:34:43
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2020-09-09 22:02:02
 */
namespace SwooleTar\Listener;

use SwooleTar\Foundation\Application;

abstract class Listener
{
    protected $flag = 'listener';

    protected $app = null;

    /**
     * @description: 注册事件
     * @param {type} 
     * @return {type} 
     * @author: gang
     */
    public abstract function handler();


    /**
     * @description: 绑定注册事件的唯一标示
     * @param {type} 
     * @return string 
     * @author: gang
     */
    public function getName()
    {
        return $this->flag;
    }
    
    public function __construct(Application $app)
    {   
        $this->app = $app;
    }
    
}