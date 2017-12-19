<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/11
 * Time: 17:51
 */

namespace Modules\Message\Facades;

use Illuminate\Support\Facades\Facade;

class SystemFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SystemService';
    }
}