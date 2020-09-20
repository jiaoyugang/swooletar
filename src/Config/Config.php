<?php
/*
 * @Description: 
 * @Version: 2.0
 * @Autor: gang
 * @Date: 2020-08-25 20:10:32
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2020-09-08 22:51:50
 */
namespace SwooleTar\Config;

class Config
{
    /**
     * 存储解析的配置项
     */
    protected $mapConfig = [];

    /**
     * 配置文件路径
     */
    protected $pathConfig = null;

    /**
     * 初始化配置文件所在目录
     */
    public function __construct()
    {
        $this->pathConfig = app()->getBasePath().'/config';
        $this->parseConfg();
    }

    /**
     * 解析配置文件(
     *    仅支持.php文件的配置文件
     *    且只能解析一级目录下的文件
     * )
     */
    protected function parseConfg()
    {
        $dir = scandir($this->pathConfig);
        foreach($dir as $key => $file){
            if($file == '.' || $file == '..'){
                continue;
            }
            $filename = pathinfo($file);
            //判读配置文件类型
            // debug([$filename['extension'],'php']);
            if(strcasecmp($filename['extension'],'php') <> 0){
                continue;
            }
            // debug($filename['filename']);
            $this->mapConfig[$filename['filename']] = require_once($this->pathConfig.'/'.$file) ;
        }
    }

    /**
     * @description: 获取配置信息
     * @param {type} 
     * @return {type} 
     */
    public function get($keys)
    {
        $data = $this->mapConfig;
        foreach (\explode('.', $keys) as $key => $value) {
            $data = $data[$value];
        }
        return $data;
    }
}