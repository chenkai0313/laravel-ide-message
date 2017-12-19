<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1',function ($api) {
    #无需身份验证
    $api->group(['namespace' => 'Modules\Message\Http\Controllers','prefix' => 'message'], function ($api) {
        #定时推送脚本接口
        $api->post('message-timed', 'MessageController@messageTimed');

        #推送、消息、短信列表-后台接口
        $api->post('message-list', 'MessageController@messageList'); // 短消息列表
        $api->post('push-list', 'MessageController@pushList');       // 推送列表
        $api->post('message-announce-top', 'MessageController@announceTop');            // 消息 最高置顶公告信息
        $api->post('message-announcelist-backend', 'MessageController@announceList');   // 消息 公告列表
        $api->post('message-noticelist-backend', 'MessageController@noticeList');       // 消息 通知列表
        $api->post('message-smslist-backend', 'SmsController@smsList');                 // 短信列表

        #推送、消息等-app接口
        $api->post('message-announcelist-api', 'AppMessageController@announceList');    // app 公告列表
        $api->post('message-noticelist-api', 'AppMessageController@noticeList');        // 通知
        $api->post('message-unread-number', 'AppMessageController@noticeUnRead');       // 通知未读数

        #公共入口
        $api->post('message-send', 'MessageController@sendMessage');        // 推送、短信发送公共入口

        $api->post('message-push', 'MessageController@push');               // 推送、消息群发
        $api->post('message-push-new', 'MessageController@pushNew');        // 推送、消息群发-重构-new
        $api->post('message-sms', 'SmsController@sms');                     // 短信发送-走队列
        $api->post('message-sms-new', 'SmsController@smsNew');              // 短信发送-走队列-new
        $api->post('message-sms-noqueue', 'SmsController@smsNoQueue');      // 短信发送-不走队列

        $api->post('message-set-read', 'MessageController@noticeRead');     // 设置消息已读
        $api->post('message-delete', 'MessageController@noticeDelete');     // 删除通知
        $api->post('get-first-announcement', 'MessageController@getFirstAnnouncement'); // 最新公告

        #pc
        $api->post('message-announce-pc', 'PcMessageController@pcAnnounceList');        // 公告列表-pc

        #测试、调试
        $api->get('schedule', 'MessageController@scheduleMessage');         // 定时脚本 自己测试

        $api->get('email-text', 'MailController@mailText');                 // 发送纯文本邮件测试
        $api->get('email-attach', 'MailController@mailAttach');             // 发送图片类型附件邮件

        #redis
        $api->post('message-redis-push', 'RedisMessageController@pushBaseRedis');       // redis设置
    });

});