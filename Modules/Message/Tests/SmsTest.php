<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/21
 * Time: 13:28
 */

namespace Modules\Message\Tests;

use Modules\Message\Tests\Common\MessageTestCase;

class SmsTest extends MessageTestCase
{
    public $params;

    /**
     * 创建测试所用对象
     */
    public function setUp()
    {
        parent::setUp();
        $this->init();
    }

    /**
     * 测试所用数据
     */
    public function readyApiParams()
    {
        $params = [];

        #短信列表
        $params['sms_list'] = [
            'method' => 'post',
            'uri' => '/message/message-smslist-backend',
            'params' => [
                'keyword'=>'15988346742'
            ],
        ];

        #短信列表-不走队列
        $params['sms_data'] = [
            'method' => 'post',
            'uri' => '/message/message-sms-noqueue',
            'params' => [
                'mobile' => json_encode(["15988346742"]),
                'content' => '您的验证码为5326。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！',
                'send_template' => 'security_code',
                'data' => json_encode(['number'=>rand(1000,9999)]),
            ],
        ];

        return $params;
    }

    // 短息列表
    public function testSmsList()
    {
        $this->apiTest($this->params['sms_list']);
    }

    // 短信发送-不走队列
    public function testSendSmsNoqueue(){
        $sms_data = $this->params['sms_data'];
        // $this->apiTest($sms_data);
        $assert = in_array("15988346742",json_decode($sms_data['params']['mobile'],true)) ? 1 : 0;
        $this->assertEquals(1,$assert);
    }

    // 短信发送-走队列
    public function testSendSms(){
        $sms_data = $this->params['sms_data'];
        // $this->apiTest($sms_data);
        $assert = in_array("15988346742",json_decode($sms_data['params']['mobile'],true)) ? 1 : 0;
        $this->assertEquals(1,$assert);
    }


}
