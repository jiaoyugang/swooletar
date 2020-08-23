<?php
/**
 * 帮助函数
 */

use SwooleTar\Foundation\Application;

if(!function_exists('app')){

    /**
     *  获取容器示例对象
     * @param  $index 容器注入标示
     * @return Application
     */
    function app($index = null)
    {
        if(empty($index)){
            return Application::getInstance();
        }
        return Application::getInstance()->make(ucfirst($index));
    }
}

if(!function_exists('config')){

    /**
     *  获取容器示例对象
     * @param  $index 容器注入标示
     * @return Application
     */
    function config($index = null)
    {
        if(empty($index)){
            return Application::getInstance();
        }
        return Application::getInstance()->make(ucfirst($index));
    }
}

 
if(!function_exists('debug')){
    /**
     * 断点输出
     *
     * @param [type] $data   打印的数据
     * @param boolean $flag  是否格式化输出
     * @return void
     */
    function debug($data,$flag = false)
    {
        if($flag){
            echo "<pre/>";
            print_r($data);
        }else{
            var_dump($data);
        }
    }
}