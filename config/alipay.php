<?php
return [

	//合作身份者id，以2088开头的16位纯数字。
	'seller_id' => '2088521146685204',

    'app_id' => '2017081408186968',

    //签名方式
    'sign_type' => 'RSA2',

    'seller_email' => 'weknet@163.com',

    // 商户私钥。
    'private_key_path' => __DIR__ . '/key/rsa_private_key.pem',

    // 阿里公钥。
    'public_key_path' => __DIR__ . '/key/alipay_public_key.pem',

    // 服务器异步通知页面路径。
    'notify_url' => 'api/webNotify',

    // 页面跳转同步通知页面路径。
    'return_url' => 'api/webReturn',

];
