<?php
/**
 * Created by PhpStorm.
 * User: 曹晗
 * Date: 2017/7/31
 * Time: 17:54
 */
return [
    #用户表 wk_users
    'user' => [
        'user-val' => [
            'user_mobile' => '手机号',
            'type' => '类型',
            'user_password' => '用户密码',
            'confirm_password' => '确认密码',
            'code' => '验证码',
            'pay_password' => '交易密码',
            'confirm_pay_pwd' => '确认交易密码'
        ],
        'user-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'regex' => ':attribute格式不正确',
            'same' => '密码和确认密码不一致',
            'unique' => ':attribute已被注册',
        ],
        #用户注册
        'user-add' => [
            'user_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'user_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:confirm_password'),
            'confirm_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:user_password'),
            'code' => 'required'
        ],
        #后台注册用户
        'user-add-backend' => [
            'user_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'user_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:user_password'),
            'real_name' => 'required',
            'user_idcard' => 'required'
        ],
        #用户登录
        'user-login' => [
            'user_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'user_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required'),
        ],
        #用户找回密码
        'user-forgot' => [
            'user_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'user_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:confirm_password'),
            'confirm_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:user_password'),
            'code' => 'required'
        ],
        #用户重置交易密码
        'user-editpaypwd' => [
            'pay_password' => 'required|integer|same:confirm_pay_pwd',
            'confirm_pay_pwd' => 'required|same:pay_password',
            'code' => 'required',
        ],
        #用户换绑手机号
        'user-changemobile' => [
            'user_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'code' => 'required',
        ],
        #用户登录后更改手机号
        'user-changepassword' => [
            'user_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:confirm_password'),
            'confirm_password' => array('regex:/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/', 'required', 'same:user_password'),
            'code' => 'required'
        ],
    ],
    'user_card' => [
        'user_card-val' => [
            'card_number' => '银行卡卡号',
        ],
        'user_card-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'unique' => ':attribute已经被绑定',
        ],
        'user_card-add' => [
            'card_number' => 'required|integer|unique:user_card',
            'code' => 'required'
        ],
    ],
    #用户详情表
    'user_info' => [
        'user_info-val' => [
            'user_education' => '学历',
            'user_profession' => '职业',
            'user_company' => '公司',
            'user_income' => '收入',
            'user_qq' => 'qq',
            'user_email' => '邮箱',
            'link_man' => '常用联系人姓名',
            'link_mobile' => '常用联系人手机号',
            'link_relation' => '与常用联系人关系',
            'province' => '省',
            'city' => '市',
            'district' => '区',
            'address' => '详细地址',
        ],
        'user_info-key' => [
            'integer' => ':attribute必须为整数',
            'regex' => ':attribute格式不正确',
            'required' => ':attribute必填',
            'email' => ':attribute格式不正确',
            'string' => ':attribute必须是文字',
            'max' => ':attribute太长'
        ],
        #添加常用联系人
        'user_info-linkman' => [
            'link_man' => 'required',
            'link_mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'link_relation' => 'required',
        ],
        #用户完善信息
        'user_info-add' => [
            'user_education' => 'max:50',
            'user_profession' => 'max:50',
            //'user_income' => '',
            'user_qq' => 'max:50',
            'user_email' => 'max:50',
            'user_company' => 'max:50',
//            'province' => 'integer',
//            'city' => 'integer',
//            'district' => 'integer',
            'address' => 'max:50',
        ],
    ],
    #address验证
    'address' => [
        #地址添加
        'address-add' => [
            'user_id' => 'required',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'street' => 'required',
            'address' => 'required',
        ],
        'address-key' => [
            'required' => ':attribute为必填项',
            'min' => ':attribute长度不符合要求',
            'integer' => ':attribute必须是数字',
        ],
        'address-val' => [
            'user_id' => '用户ID',
            'province' => '省份',
            'city' => '市',
            'district' => '区',
            'street' => '街道',
            'address' => '详细地址',
        ],
        #地址编辑
        'address-edit' => [
            'user_id' => 'required',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'street' => 'required',
            'address' => 'required',
        ],
    ],
];