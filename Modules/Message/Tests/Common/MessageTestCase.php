<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/21
 * Time: 9:37
 */

namespace Modules\Message\Tests\Common;

use Tests\Unit\BaseTestCase;

class MessageTestCase extends BaseTestCase
{
    public $complete_url = true;

    #默认header请求头
    protected $default_header = [];
//    protected $default_header = ['Accept' => 'application/vnd.riskcontrol.v1+json'];

    #默认user登陆信息
    /*public $login_params = [
        'backend' => [
            'method' => 'post',
            'uri' => '/backend/admin-login',
            'params' => [
                'admin_name' => 'admin',
                'admin_password' => '111111'
            ],
        ],
    ];*/
}