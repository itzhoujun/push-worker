<?php

namespace PushWorker\push;
/**
 * Created by PhpStorm.
 * User: zhoujun
 * Date: 16/12/21
 * Time: 21:04
 */
abstract class Push
{
    public static $push_names = array(
        'ios' => 'IosPush',
        'jpush' => 'JPush',
        'alipush' => 'AliPush',
    );

    public static function init($push_name){
        if(isset(self::$push_names[$push_name])){
            return new self::$push_names[$push_name];
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

    /**
     * @param array $config
     * @return mixed
     * 设置推送的配置
     */
    protected abstract function setConfig(array $config);
}