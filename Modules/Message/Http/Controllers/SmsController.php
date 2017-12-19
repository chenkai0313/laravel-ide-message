<?php
/**
 * Created by PhpStorm.
 * User: pc06
 * Date: 2017/9/1
 * Time: 9:52
 */

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Mail;

class SmsController extends Controller
{
    /**
     * 短信发送-走队列
     */
    public function sms(Request $request){
        $params = $request->input();
        $result = \SmsService::queueSendEntry($params);
        return $result;
    }

    // 重构入口
    public function smsNew(Request $request){
        $params = $request->input();
        $data = json_decode($params['json'],true);
        return $data;
        $result = \SmsService::queueSendEntry($params);
        return $result;
    }

    /**
     * 短信发送-不走队列
     */
    public function smsNoQueue(Request $request){
        $params = $request->input();
        $result = \SmsService::sendEntry($params);
        return $result;
    }

    /**
     * 短信列表
     */
    public function smsList(Request $request){
        $params = $request->input();
        $result = \SmsService::smsList($params);
        return $result;
    }
}