<?php
/**
 * Created by PhpStorm.
 * User: pc06
 * Date: 2017/9/1
 * Time: 9:49
 */

namespace Modules\Message\Facades;

use Illuminate\Support\Facades\Facade;

class SmsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SmsService';
    }
}