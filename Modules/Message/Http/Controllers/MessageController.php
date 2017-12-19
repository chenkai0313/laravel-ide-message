<?php

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MessageController extends Controller
{
    /**
     * 推送列表
     */
    public function pushList(Request $request){
        $params = $request->input();
        $result = \MessageService::messagePush($params);
        return $result;
    }

    /**
     * 消息列表-all
     */
    public function messageList(Request $request){
        $params = $request->input();
        $result = \MessageService::messageList($params);
        return $result;
    }

    /**
     * 消息列表-公告
     */
    public function announceList(Request $request){
        $params = $request->input();
        $result = \MessageService::messageAnnounceBackend($params);
        return $result;
    }

    /**
     * 消息列表-通知
     */
    public function noticeList(Request $request){
        $params = $request->input();
        $result = \MessageService::messageNoticeBackend($params);
        return $result;
    }

    /**
     * 置顶公告
     */
    public function announceTop(Request $request){
        $params = $request->input();
        $result = \MessageService::messageTop($params);
        return $result;
    }

    // 通知设置已读
    public function noticeRead(Request $request) {
        $params = $request->input();
        $result = \MessageService::messageSetRead($params);
        return $result;
    }

    // 通知删除
    public function noticeDelete(Request $request) {
        $params = $request->input();
        $result = \MessageService::messageDelete($params);
        return $result;
    }

    /**
     * 定时推送接口-处理推送数据
     */
    public function messageTimed(Request $request){
        $params = $request->input();
        $result = \MessageService::scheduleMessage($params);
        return $result;
    }

    /**
     * 后台推送
     */
    public function push(Request $request){
        $params = $request->input();
        $result = \MessageService::sendEntry($params);
        return $result;
    }

    /**
     * 后台推送-重构（新）- 2017-10-11
     */
    public function pushNew(Request $request){
        $params = $request->input();
        $data = json_decode($params['json'],true);
        return $data;
        $result = \PushService::sendEntryNew($data);
        return $result;
    }

    /**
     * 最新公告
     */
    public function getFirstAnnouncement(Request $request){
        $params = $request->input();
        $result = \MessageService::getFirstAnnouncement($params);
        return $result;
    }

    public function scheduleMessage(Request $request){
        $params = $request->input();
        $result = \MessageService::scheduleMessage($params);
        return $result;
    }

    /**
     * 消息服务发送公共入口-推送、短信
     */
    public function sendMessage(Request $request){
        $params = $request->input();
        $data = json_decode($params['json'],true);
        // common_log('message','消息服务传参',$params,$data);
        $result = \MessageService::messgaeSendEntry($data);
        return $result;
    }
}
