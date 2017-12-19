<?php
/**
 * Created by PhpStorm.
 * User: 曹晗
 * Date: 2017/7/29
 * Time: 14:07
 */
return [
    #广告分类
    'adtype' => [
        'adtype-val' => [
            'type_name' => '广告分类名',
            'img_size' => '广告图片大小'
        ],
        'adtype-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
        ],
        #广告分类的编辑
        'adtype-edit' => [
            'type_id' => 'required|integer',
            'type_name' => 'required',
            'img_size' => 'required',
        ],
        #广告分类的添加
        'adtype-add' => [
            'type_name' => 'required',
            'img_size' => 'required',
        ]
    ],
    #广告表
    'ad' => [
        'ad-val' => [
            'type_id' => '广告分类',
            'ad_id' => '广告ID',
            'ad_img' => '广告图片',
            'is_show' => '是否显示'
        ],
        'ad-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
        ],

        #广告的添加
        'ad-add' => [
            'type_id' => 'required|integer',
            'ad_img' => 'required',
            'is_show' => 'required',
        ],
        #广告的编辑
        'ad-edit' => [
            'ad_id' => 'required|integer',
            'type_id' => 'required|integer',
            'ad_img' => 'required',
            'is_show' => 'required',
        ]
    ],


    #内容类型验证
    'articletype' => [
        #类型添加
        'articletype-add' => [
            'type_name' => 'required',
            'parent_id' => 'required|integer',
        ],
        #类型编辑
        'articletype-edit' => [
            'type_id' => 'required',
            'parent_id' => 'required|integer',
        ],
        'articletype-key' => [
            'required' => ':attribute为必填项',
            'min' => ':attribute长度不符合要求',
            'integer' => ':attribute必须是数字',
            'unique' => ':attribute必须唯一'
        ],
        'articletype-val' => [
            'type_name' => '类型名称',
            'parent_id' => '父级ID',
        ]
    ],
    #文章管理验证
    'article' => [
        #文章添加
        'article-add' => [
            'article_title' => 'required',
//            'article_content' => 'required',
            'type_id' => 'required',
        ],
        #文章编辑
        'article-edit' => [
            'article_id' => 'required',
        ],
        'article-key' => [
            'required' => ':attribute为必填项',
            'min' => ':attribute长度不符合要求',
            'integer' => ':attribute必须是数字',
            'unique' => ':attribute必须唯一'
        ],
        'article-val' => [
            'article_id' => '文章ID',
            'article_title' => '文章标题',
            'article_content' => '文章内容',
            'type_id' => '类型ID',
            'admin_id' => '操作员ID'
        ]
    ],

    #sms验证
    'sms' => [
        'sms-val' => [
            'mobile' => '手机号',
            'type' => '类型',
            'code' => '验证码'
        ],
        'sms-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
            'regex' => ':attribute格式不正确',
            'unique' => ':attribute已被注册',
        ],
        #发送短信
        'sms-send' => [
            'mobile' => array('regex:/^1[34578]+\d{9}$/', 'required'),
            'type' => 'required'
        ]
    ],
    #constanttype验证
    'constanttype' => [
        'constanttype-val' => [
            'type' => '常量类型',
        ],
        'constanttype-key' => [
            'required' => ':attribute必填',
            'unique' => ':attribute唯一',
        ],
        #发送短信
        'constanttype-add' => [
            'type' => 'required|unique:system_constant_type'
        ]
    ],
    #短信模板验证
    'smstemplate' => [
        'smstemplate-val' => [
            'keyword_id' => '短信模板关键字ID',
            'keyword_name' => '短信模板关键字',
            'content'=>'短信模板内容',
            'prepare_node'=>'预发节点',
            'type'=>'短信类型',
            'id'=>'短信模板ID',
        ],
        'smstemplate-key' => [
            'integer' => ':attribute必须为整数',
            'required' => ':attribute必填',
        ],
        #短信类型的添加
        'smstemplatetype-add' => [
            'keyword_name' => 'required',
        ],
        #短信类型的编辑
        'smstemplatetype-edit' => [
            'keyword_id' => 'required',
            'keyword_name' => 'required',
        ],
        #短信模板的添加
        'smstemplate-add' => [
            'content' => 'required',
            'prepare_node'=>'required',
            'type'=>'required',
        ],
        #短信模板的修改
        'smstemplate-edit' => [
            'content' => 'required',
            'prepare_node'=>'required',
            'type'=>'required',
            'id'=>'required',
        ],

    ],


];