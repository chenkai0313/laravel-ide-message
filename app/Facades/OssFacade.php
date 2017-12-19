<?php

namespace app\Facades;

use Illuminate\Support\Facades\Facade;


class OssFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'third.oss';
    }
}
