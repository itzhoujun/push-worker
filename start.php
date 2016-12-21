<?php

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

require_once __DIR__ . '/Worker.php';

date_default_timezone_set('Asia/Shanghai');

Worker::runAll();
