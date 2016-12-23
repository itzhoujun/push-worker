<?php

namespace PushWorker\push\Utils;
use Exception;

abstract class Push
{
    protected $_package_name;
    protected $_production_mode;
    protected $_config;
    protected $_class_name;

    protected $_target;
    protected $_platform;
    protected $_message;
    protected $_extras;
    protected $_xiaomi_activity;

    public static $push_names = array(
        'ios' => 'IosPush',
        'jpush' => 'JPush',
        'alipush' => 'AliPush',
    );

    public function __construct($package_name){
        $push_config = include 'push/conf/config.php';
        $this->_package_name = $package_name;
        $cls = get_class($this);
        $arr = explode("\\", $cls);
        $this->_class_name = $arr[count($arr)-1];
        if(isset($push_config[strtoupper($this->_class_name)])){
            $this->_config = $push_config[strtoupper($this->_class_name)][$this->_package_name];
        }
    }

    public static function init($push_name,$package_name){
        if(isset(self::$push_names[$push_name])){
            $cls = '\\PushWorker\\push\\Utils\\'. self::$push_names[$push_name];
            echo $cls;
            return new $cls($package_name);
        }else{
            throw new Exception('push class not exists');
        }
    }

    /**
     * @param array $tokens
     * @param $message
     * @param array $extras
     * @return mixed
     * 执行推送操作
     */
    protected abstract function exec(array $tokens,$message,$extras = array());
}