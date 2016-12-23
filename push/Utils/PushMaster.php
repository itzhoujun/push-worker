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
        $push_total_count = 0;
        if(empty($data)){
            return;
        }
        foreach ($data as $item){
            $push = Push::init('jpush', $item->package_name);
            $push_count = $push->exec(json_decode($item->target,true), $item->message,json_decode($item->extras,true));
            if($push_count){
                $push_total_count += $push_count;
                //todo 删除
            }else{
                return;
            }
        }
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