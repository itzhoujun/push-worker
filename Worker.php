<?php

//namespace Push;

class Worker{

    public static $pid_file = '';

    public static $log_file = '';

    public static $workers = array();

    public static function run(){
        self::checkEnv();
        self::init();
        self::parseCommand();
        self::daemonize();
    }

    protected static function checkEnv(){
        if(php_sapi_name() != 'cli'){
            exit('only run in command line mode!');
        }
    }

    protected static function init(){
        if(empty(self::$pid_file)){
            self::$pid_file = __DIR__ . '/temp/worker.pid';
        }

        if(empty(self::$log_file)){
            self::$log_file = __DIR__ . '/temp/worker.log';
        }
    }

    protected static function parseCommand(){

        global $argv;

        if (!isset($argv[1])) {
            exit("Usage: php yourfile.php {start|stop|restart|reload|status}\n");
        }
        $command = $argv[1];
        //check master is exist
        $master_id = @file_get_contents(self::$pid_file);
        $master_is_alive = $master_id && posix_kill($master_id,0);

        if($master_is_alive){
            if($command == 'start' && posix_getpid() != $master_id){
                exit('master is already running!');
            }
        }
        self::log('worker ' . $command);

        switch ($command) {
            case 'start':
                break;
            case 'status':

                exit(0);
            case 'stop':

                break;
            default:
                exit("Usage: php yourfile.php {start|stop|restart|reload|status}\n");
                break;
        }


    }

    protected static function daemonize(){
        umask(0);
        $pid = pcntl_fork();
        if($pid == -1){
            throw new Exception("fork fail");
        }elseif($pid > 0){
            exit(0);
        }else{
            echo "111 \n";
        }
    }

    protected static function forkOneWorker(){
        $pid = pcntl_fork();
        if($pid > 0){

        }
    }

    protected static function log($message){
        $message = date('Y-m-d H:i:s') . ' pid:' . posix_getpid() .' ' . $message . "\n";
        file_put_contents((string)self::$log_file,$message,FILE_APPEND | LOCK_EX);
    }

}
