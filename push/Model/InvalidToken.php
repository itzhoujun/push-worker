<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22 0022
 * Time: 15:07
 */

namespace PushWorker\push\Model;


use Illuminate\Database\Eloquent\Model;

class InvalidToken extends Model
{

    public function getList(){
        var_dump(InvalidToken::all()->toArray());
    }
}