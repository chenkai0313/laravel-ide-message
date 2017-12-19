<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/11
 * Time: 15:15
 */

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PcMessageController extends Controller
{
    //pc公告列表 - 取最高置顶三条
    public function pcAnnounceList(Request $request) {
        $params = $request->input();
        $result = \MessageService::messageAnnounceBackend($params);
        return $result;
    }
}