<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/28
 * Time: 12:59
 */

namespace Modules\Message\Facades;

use Illuminate\Support\Facades\Facade;

class MailFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MailService';
    }
}