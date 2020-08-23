<?php
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
        // debug($this->pathConfig);
        $this->parseConfg();
        // debug(app('config'));
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
        // debug($dir);
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
     * 获取配置文件信息
     */
    public function getConfig($index)
    {
        $config = $this->mapConfig;
        $index = explode('.',$index);
        return $this->scanIndex($config,$index);
    }

    /**
     * 递归遍历获取配置信息
     */
    public function scanIndex( $config ,$index)
    {
        // debug($config);
        foreach($config as $key => $val){
            if(in_array($key,$index)){
                if(is_string($val) || is_int($val) || is_bool($val)){
                    return $val;
                }else{
                    return $this->scanIndex($val , $index);
                }
                
            }
        }
    }

}