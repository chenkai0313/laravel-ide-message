<?php
/**
 * 订单模块 验证器
 * Author: 葛宏华
 * Date: 2017/8/2
 */
return [
    #订单表
    'order_info' => [
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
    ]
];