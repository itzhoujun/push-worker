<?php
namespace PushWorker\push\Model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22 0022
 * Time: 14:45
 */
class Config extends \Illuminate\Database\Eloquent\Model
{
    public function getList()
    {
        var_dump(Config::all()->toArray());

    }
}