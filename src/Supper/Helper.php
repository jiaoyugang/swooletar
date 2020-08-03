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

        return Application::getInstance()->make($index);
    }
 }