<?php
namespace PushWorker;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use PushWorker\push\Utils\Push;
use PushWorker\push\Utils\PushMaster;

class Worker
{

    public static $pid_file = '';

    public static $log_file = '';

    public static $status_file = '';

    public static $master_pid = 0;

    public static $stdoutFile = '/dev/null';

    public static $workers = array();

    public static $worker_num = 3;

    public static $worker_names = array('ios', 'jpush', 'alipush');

    public static $worker_name = '';

    public static $status = 0;

    const STATUS_RUNNING = 1;
    const STATUS_SHUTDOWN = 2;

    public static function runAll()
    {
        self::checkEnv();
        self::init();
        self::parseCommand();
        self::daemonize();
        self::installSignal();
        self::saveMasterPid();
        self::resetStd();
        self::forkWorkers();
        self::monitorWorkers();
    }

    protected static function checkEnv()
    {
        if (php_sapi_name() != 'cli') {
            exit('only run in command line mode!');
        }
    }

    protected static function init()
    {
        $temp_dir = sys_get_temp_dir() . '/push_worker';

        if (!is_dir($temp_dir) && !mkdir($temp_dir)) {
            exit('mkdir runtime fail');
        }

        if (empty(self::$status_file)) {
            self::$status_file = $temp_dir . '/status_file';
        }

        if (empty(self::$pid_file)) {
            self::$pid_file = $temp_dir . '/worker.pid';
        }

        if (empty(self::$log_file)) {
            self::$log_file = $temp_dir . '/worker.log';
        }
    }

    protected static function parseCommand()
    {
        global $argv;

        if (!isset($argv[1])) {
            exit("Usage: php yourfile.php {start|stop|restart|reload|status}\n");
        }
        $command = $argv[1];
        //检测master进程是否存货
        $master_id = @file_get_contents(self::$pid_file);
        $master_is_alive = $master_id && posix_kill($master_id, 0);

        if ($master_is_alive) {
            if ($command == 'start' && posix_getpid() != $master_id) {
                exit('push worker is already running!' . PHP_EOL);
            }
        } else {
            if ($command != 'start') {
                exit('push worker not run!' . PHP_EOL);
            }
        }
        switch ($command) {
            case 'start':
                break;
            case 'status':
                if (is_file(self::$status_file)) {
                    @unlink(self::$status_file);
                }
                posix_kill($master_id, SIGUSR2);
                usleep(300000);
                @readfile(self::$status_file);
                exit(0);
            case 'stop':
                //向主进程发出stop的信号
                self::log('push worker[' . $master_id . '] stopping....');
                $master_id && $flag = posix_kill($master_id, SIGINT);
                while ($master_id && posix_kill($master_id, 0)) {
                    usleep(300000);
                }
                self::log('push worker[' . $master_id . '] stop success');
                exit(0);
                break;
            default:
                exit("Usage: php yourfile.php {start|stop|restart|reload|status}\n");
                break;
        }


    }

    protected static function daemonize()
    {
        umask(0);
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception("fork fail");
        } elseif ($pid > 0) {
            exit(0);
        } else {
            if (-1 === posix_setsid()) {
                throw new Exception("setsid fail");
            }
            self::setProcessTitle('push worker: master');
        }
    }

    protected static function saveMasterPid()
    {
        self::$master_pid = posix_getpid();
        if (false === @file_put_contents(self::$pid_file, self::$master_pid)) {
            throw new Exception('fail to save master pid: ' . self::$master_pid);
        }
    }

    protected static function forkWorkers()
    {
        while (count(self::$workers) < self::$worker_num) {
            $curr_name = current(self::$worker_names);
            if (!in_array($curr_name, array_values(self::$workers))) {
                self::forkOneWorker($curr_name);
                next(self::$worker_names);
            }
        }
    }

    protected static function installSignal()
    {
        pcntl_signal(SIGINT, array('\\PushWorker\\Worker', 'signalHandler'), false);
        pcntl_signal(SIGUSR2, array('\\PushWorker\\Worker', 'signalHandler'), false);
    }

    public static function signalHandler($signal)
    {
        switch ($signal) {
            case SIGINT: // Stop.
                self::stopAll();
                break;
            case SIGUSR1:
                break;
            case SIGUSR2: // Show status.
                self::writeStatus();
                break;
        }
    }

    protected static function writeStatus()
    {
        $pid = posix_getpid();
        if (self::$master_pid == $pid) {
            $master_alive = self::$master_pid && posix_kill(self::$master_pid, 0);
            $master_alive = $master_alive ? 'is running' : 'die';
            file_put_contents(self::$status_file, 'master[' . self::$master_pid . '] ' . $master_alive . PHP_EOL, FILE_APPEND | LOCK_EX);
            foreach (self::$workers as $pid => $worker_name) {
                posix_kill($pid, SIGUSR2);
            }
        } else {
            $name = self::$worker_name . ' worker[' . $pid . ']';
            $alive = $pid && posix_kill($pid, 0);
            $alive = $alive ? 'is running' : 'die';
            file_put_contents(self::$status_file, $name . ' ' . $alive . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    protected static function forkOneWorker($worker_name)
    {

        $pid = pcntl_fork();
        if ($pid > 0) {
            self::$workers[$pid] = $worker_name;
        } elseif ($pid == 0) {
            self::$worker_name = $worker_name;
            self::log($worker_name . ' push worker start');
            self::setProcessTitle('push worker: ' . $worker_name);
            self::run();
        } else {
            throw new Exception('fork one worker fail');
        }
    }

    protected static function resetStd()
    {
        global $STDOUT, $STDERR;
        $handle = fopen(self::$stdoutFile, "a");
        if ($handle) {
            unset($handle);
            @fclose(STDOUT);
            @fclose(STDERR);
            $STDOUT = fopen(self::$stdoutFile, "a");
            $STDERR = fopen(self::$stdoutFile, "a");
        } else {
            throw new Exception('can not open stdoutFile ' . self::$stdoutFile);
        }
    }

    protected static function monitorWorkers()
    {
        self::$status = self::STATUS_RUNNING;
        while (1) {
            pcntl_signal_dispatch();
            $status = 0;
            $pid = pcntl_wait($status, WUNTRACED);
            pcntl_signal_dispatch();
            //child exit
            if ($pid > 0) {
                if (self::$status != self::STATUS_SHUTDOWN) {
                    $worker_name = self::$workers[$pid];
                    unset(self::$workers[$pid]);
                    self::forkOneWorker($worker_name);
                }
            }
        }

    }

    protected static function run()
    {
        Timer::init();
        Timer::add(5, function () {
            if(self::$worker_name == 'ios'){
                PushMaster::executeIosPush();
            }elseif(self::$worker_name == 'jpush'){
                PushMaster::executeJPush();
            }elseif(self::$worker_name == 'alipush'){
                PushMaster::executeAliPush();
            }
        });
        Timer::tick();
        while (1) {
            pcntl_signal_dispatch();
            sleep(1);
        }
    }

    protected static function setProcessTitle($title)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        }
    }

    protected static function stopAll()
    {
        $pid = posix_getpid();
        if (self::$master_pid == $pid) { //master
            self::$status = self::STATUS_SHUTDOWN;
            foreach (self::$workers as $pid => $worker_name) {
                //停止worker进程
                posix_kill($pid, SIGINT);
            }
            //停止master进程
            @unlink(self::$pid_file);
            exit(0);
        } else { //child
            self::log('push worker ' . self::$worker_name . ' pid: ' . $pid . ' stop');
            exit(0);
        }
    }

    protected static function log($message)
    {
        $message = date('Y-m-d H:i:s') . ' pid:' . posix_getpid() . ' ' . $message . "\n";
        file_put_contents((string)self::$log_file, $message, FILE_APPEND | LOCK_EX);
    }

}
