<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22 0022
 * Time: 10:26
 */

//require_once 'Push.php';
//require_once 'JPush.php';
//require_once 'DbUtil.php';
//
////$push = \PushWorker\Push::init('alipush', 'co.sihe.tigerlottery');
////$result = $push->exec(array('2f92916ebf404b34bea6f350a8540085'),'test');
//
//$db = new \PushWorker\DbUtil();
//$db->connect();
//$sql = 'select * from config';
//$result = $db->search($sql,array('id','name','value','code'));
//$db->close();
//var_dump($result);


$loader = require 'vendor/autoload.php';
//$loader->setPsr4('JPush\\',  'vendor/Jpush/jpush/jpush/src/JPush');
$loader->setPsr4('PushWorker\\',  './');
$loader->setPsr4('', __DIR__ . 'vendor/');

//
$db = new Illuminate\Database\Capsule\Manager();
$db_conf = require 'push/conf/db.php';
$db->addConnection($db_conf);
$db->setAsGlobal();
$db->bootEloquent();
use \Illuminate\Database\Capsule\Manager as DB;
use \PushWorker\push\Utils\Push;
$data = DB::table('push')->where('platform','=','1')->first();
$push = Push::init('jpush', $data->package_name);
$result = $push->exec(json_decode($data->target,true), $data->message);
