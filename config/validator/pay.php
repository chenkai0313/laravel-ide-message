<?php
/**
 * 支付模块 验证器
 * Author: 傅跃华
 * Date: 2017/8/14
 */
return [
    #支付
    'pay' => [
        'pay-val' => [
            'order_sn' => '订单编号',
            'return_url' => '回调网址'
        ],
        'pay-key' => [
            'required' => ':attribute必填',
            'url' => ':attribute必须是URL格式'
        ],
        #支付宝电脑网页支付
        'alipay-web' => [
            'order_sn' => 'required',
            'return_url' => 'required|url',
        ],
    ]
];