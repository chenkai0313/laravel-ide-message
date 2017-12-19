<?php
/**
 * 消息验证
 * Author: 叶帆
 * Date: 2017/8/31
 */

return [
    'message' => [
        'message-val' =>[
            'services' => '消息服务',
            'send_params' => '发送消息参数',
            'object_info' => '发送对象信息'
        ],
        'message-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'regex' => ':attribute内容不符合要求',
        ],
        'message-send' => [
            'services' => 'required',
            'send_params' => 'required',
            'object_info' => 'required',
        ],
    ],

    #推送信息格式
    'push' => [
        'push-val' => [
            'type' => '消息主类型',
            'message_type' => '消息子类型',
            'operate_type' => '操作类型',
            /*'audience' => '消息对象',*/
            'send_object' => '消息对象',
            'user_id' => '对象id',
            /*'tags' => '标签',*/
            'group_tags' => '标签',
            'title' => '标题',
            /*'description' => '内容',*/
            'content' => '内容',
            'platform' => '推送平台',
            'is_direct' => '是否直推',
            'send_time' => '发送时间',
            'result' => '推送主体',
        ],

        'push-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'regex' => ':attribute内容不符合要求',
        ],
        #消息发送
        'push-send' => [
            'type' => array('regex:/^[1-3]{1}$/', 'required', 'integer'),
            'operate_type' => array('regex:/^[1-3]{1}$/', 'required', 'integer'),
            'send_object' => 'required',
            'title' => 'required',
            'content' => 'required',
        ],
    ],

    #sms验证
    'sms' => [
        'sms-val' => [
            'user_mobile' => '手机号',
            'content' => '短信内容',
            'send_template' => '短信模板'
        ],
        'sms-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'regex' => ':attribute格式不正确',
            'unique' => ':attribute已被注册',
        ],
        #发送短信
        'sms-send' => [
            'user_mobile' => 'required',
            'content' => 'required',
            'send_template' => 'required'
        ]
    ],
];