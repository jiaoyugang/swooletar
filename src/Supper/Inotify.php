<?php
namespace SwooleTar\Supper;

/**
 * 文件监听类
 * 
 */
class Inotify
{
    /**
     * innotify初始化句柄
     *
     * @var [type]
     */
    private $fd;

    /**
     * 监控路径
     *
     * @var [type]
     */
    private $watchPath;

    /**
     * 监听事件
     *
     * @var [type]
     */
    private $watchMask;

    /**
     * 监听回调函数
     *
     * @var [type]
     */
    private $watchHandler;

    /**
     * 
     *
     * @var boolean
     */
    private $doing        = false;
    
    /**
     * 确定需要检测的文件类型
     *
     * @var array
     */

    /**
     * 校验监听的文件类型
     *
     * @var array
     */
    private $fileTypes    = [
        '.php' => true
    ];

    private $wdPath       = [];
    private $pathWd       = [];

    public function __construct($watchPath, callable $watchHandler, $watchMask = IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE)
    {
        if (!extension_loaded('inotify')) {
            var_dump('The linux not install inotify');
            return false;
        }
        $this->fd = inotify_init();
        $this->watchMask = $watchMask;
        $this->watchHandler = $watchHandler;
        $this->watch($watchPath);
    }
    
    /**
     * 【单个】添加需要校验的文件类型
     *
     * @param [type] $type
     * @return void
     */
    public function addFileType($type)
    {
        $type = '.' . trim($type, '.');
        $this->fileTypes[$type] = true;
    }

    /**
     * 【批量】添加需要校验的文件类型
     *
     * @param array $types
     * @return void
     */
    public function addFileTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addFileType($type);
        }
    }

    /**
     * 通过递归去获取指定的目录下的文件，然后一一进行监控
     * 对每一个文件进行inotify_add_watch 文件事件注册
     *
     * 六星教育 @shineyork老师
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    protected function watch($path)
    {
        $wd = inotify_add_watch($this->fd, $path, $this->watchMask);
        if ($wd === false) {
            return false;
        }
        $this->bind($wd, $path);
        
        if (is_dir($path)) {
            $wd = inotify_add_watch($this->fd, $path, $this->watchMask);
            if ($wd === false) {
                return false;
            }
            $this->bind($wd, $path);
            // 列出 $path中 目录中的文件和目录：
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $file = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file)) {
                    // 利用递归把子文件也一并的写入
                    $this->watch($file);
                }
            }
        }
        return true;
    }

    /**
     * 移除监听对象
     *
     * @return void
     */
    protected function clearWatch()
    {
        foreach ($this->wdPath as $wd => $path) {
            inotify_rm_watch($this->fd, $wd);
        }
        $this->wdPath = [];
        $this->pathWd = [];
    }

    /**
     * 新增监听对象
     *
     * @param [type] $wd
     * @param [type] $path
     * @return void
     */
    protected function bind($wd, $path)
    {
        $this->pathWd[$path] = $wd;
        $this->wdPath[$wd] = $path;
    }

    /**
     * 取消绑定监听的对象
     *
     * @param [type] $wd
     * @param [type] $path
     * @return void
     */
    protected function unbind($wd, $path = null)
    {
        unset($this->wdPath[$wd]);
        if ($path !== null) {
            unset($this->pathWd[$path]);
        }
    }

    /**
     * 启动监听对象
     *
     * @return void
     */
    public function start()
    {
        swoole_event_add($this->fd, function ($fp) {
            $events = inotify_read($fp);
            if (empty($events)) {
                return null;
            }

            foreach ($events as $event) {
                if ($event['mask'] == "IN_IGNORED") {
                    continue;
                }

                $fileType = strchr($event['name'], '.');
                if (!isset($this->fileTypes[$fileType])) {
                    continue;
                }

                if ($this->doing) {
                    continue;
                }
                // 延迟更新，做缓冲
                swoole_timer_after(100, function () use ($event) {
                    // 回调自定义函数方法
                    call_user_func_array($this->watchHandler, [$event]);
                    //  标记当前以重启结束
                    $this->doing = false;
                });
                $this->doing = true;
                break;
            }
        });
    }

    public function stop()
    {
        swoole_event_del($this->fd);
        fclose($this->fd);
    }

    public function getWatchedFileCount()
    {
        return count($this->wdPath);
    }

    public function __destruct()
    {
        $this->stop();
    }
}
