<?php
/**
 * Created by PhpStorm.
 * User: 叶帆
 * Date: 2017/9/5
 * Time: 10:52
 */

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AppMessageController extends Controller
{
    // 短消息公告列表
    public function announceList(Request $request) {
        $params = $request->input();
        $result = \MessageService::messageAnnounce($params);
        return $result;
    }

    // 通知列表
    public function noticeList(Request $request) {
        $params = $request->input();
        $result = \MessageService::messageNotice($params);
        return $result;
    }

    // 未读总数
    public function noticeUnRead(Request $request){
        $params = $request->input();
        $result = \MessageService::messageUnRead($params);
        return $result;
    }
}