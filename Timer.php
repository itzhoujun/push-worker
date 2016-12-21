<?php


    /**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21 0021
 * Time: 16:32
 */

namespace PushWorker;
use Exception;

class Timer
{

    public static $tasks = array();

    public static function init()
    {
        pcntl_signal(SIGALRM, array('\\PushWorker\\Timer', 'signalHandle'), false);
    }

    public static function signalHandle()
    {
        pcntl_alarm(1);
        //执行任务
        if (empty(self::$tasks)) {
            return;
        }

        foreach (self::$tasks as $run_time => $task) {
            $time_now = time();
            if ($time_now >= $run_time) {
                $func = $task[0];
                $args = $task[1];
                $interval = $task[2];
                call_user_func_array($func, $args);
                unset(self::$tasks[$run_time]);
                Timer::add($interval, $func, $args);
            }
        }
    }

    public static function add($interval, $func, $args = array())
    {
        if ($interval <= 0) {
            echo new Exception('wrong interval');
            return false;
        }
        if (!is_callable($func)) {
            echo new Exception('not callable');
            return false;
        } else {
            $runtime = time() + $interval;
            self::$tasks[$runtime] = array($func, $args, $interval);
            return true;
        }
    }

    public static function tick()
    {
        pcntl_alarm(1);
    }
}