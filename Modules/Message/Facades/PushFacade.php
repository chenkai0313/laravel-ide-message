<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/12
 * Time: 14:58
 */

namespace Modules\Message\Facades;

use Illuminate\Support\Facades\Facade;

class PushFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PushService';
    }
}