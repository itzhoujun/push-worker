<?php

use Illuminate\Database\Capsule\Manager as DB;

$loader = require 'vendor/autoload.php';
$loader->setPsr4('PushWorker\\',  './');
$loader->setPsr4('JPush\\',  '/vendor/Jpush/jpush/jpush/src/JPush/');
$loader->setPsr4('', __DIR__ . '/vendor/');
$db_conf = require 'push/conf/db.php';
$db = new DB;
$db->setAsGlobal();
$db->addConnection($db_conf);
$db->bootEloquent();
use PushWorker\Worker;

if(strpos(strtolower(PHP_OS),'win') === 0){
  exit('not support window!');
}

if(!extension_loaded('pcntl')){
  exit('please install pcntl extension!');
}

if(!extension_loaded('posix')){
  exit('please install posix extension!');
}

date_default_timezone_set('Asia/Shanghai');

Worker::runAll();
