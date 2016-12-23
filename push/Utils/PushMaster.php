<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22 0022
 * Time: 18:26
 */

namespace PushWorker\push\Utils;
use Illuminate\Database\Capsule\Manager as DB;

class PushMaster
{
    public static $last_run_time = 0; //上次开始执行时间

    public static $last_run_count = 0; //上次执行条数

    public static $jpush_limit = 600; // 限制每分钟600次

    public static function executeIosPush(){
        $item = DB::table('push')->where('platform','=','2')
            ->where('push_type','=','1')
            ->where('push_brand','=','1')
            ->first();
        $push = Push::init('ios', $item->package_name);
        $connect_result = $push->connect();
        if(!$connect_result){
            // TODO log
        }else{
            $tokens = json_decode($item->target,true);
            $push_count = $push->exec($tokens, $item->message,json_decode($item->extras,true));
            //TODO log，删除已推送的数据
            $push->close();
        }
    }

    public static function executeJPush(){
        $data = DB::table('push')->where('platform','=','1')
            ->where('push_type','=','1')
            ->where('push_brand','=','2')
            ->get();
        $begin_time = time();
        $sdk_count = 0;
        $push_total_count = 0;
        if(empty($data)){
            return;
        }
        foreach ($data as $item){

            $one_minute_push_count = $push_total_count+self::$last_run_count;
            while($one_minute_push_count>=self::$jpush_limit && (time() - self::$last_run_time) < 60){
                sleep(1);
            }

            $push = Push::init('jpush', $item->package_name);
            $push_count = $push->exec(json_decode($item->target,true), $item->message,json_decode($item->extras,true));
            if($push_count){
                $sdk_count ++;
                $push_total_count += $push_count;
                //todo 删除
            }
        }
        self::$last_run_time = $begin_time;
        self::$last_run_count = $sdk_count;
    }

    public static function executeAliPush(){
        $data = DB::table('push')->where('platform','=','1')
            ->where('push_type','=','1')
            ->where('push_brand','=','3')
            ->take(100)
            ->get();
        foreach ($data as $item){
            $push = Push::init('alipush', $item->package_name);
            $push_count = $push->exec(json_decode($item->target,true), $item->message,json_decode($item->extras,true));
        }
    }
}