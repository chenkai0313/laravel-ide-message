<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17 0017
 * Time: 上午 9:31
 */
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class JpushFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'third.jpush';
    }
}