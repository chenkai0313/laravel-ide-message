<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/12
 * Time: 9:27
 */

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RedisMessageController extends Controller
{
    public function pushBaseRedis(Request $request) {
        $params = $request->input();
        $result = \SystemService::pushCommonDataRedis($params);
        return $result;
    }
}