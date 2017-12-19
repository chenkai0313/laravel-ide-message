<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/21
 * Time: 14:42
 */

namespace Modules\Message\Tests;

use Modules\Message\Tests\Common\MessageTestCase;

class ApiMessageTest extends MessageTestCase
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

        /*--------------------------------api用接口-------------------------------------*/
        #公告列表
        $params['announce_list'] = [
            'method' => 'post',
            'uri' => '/message/message-announcelist-api',
            'params' => ['type'=>1],
        ];

        #通知列表
        $params['notice_list'] = [
            'method' => 'post',
            'uri' => '/message/message-noticelist-api',
            'params' => [
                'type' => 2,
                'user_id' => 1,
            ],
        ];

        #未读通知
        $params['unread_number'] = [
            'method' => 'post',
            'uri' => '/message/message-unread-number',
            'params' => [],
        ];

        #设置已读
        $params['set_read'] = [
            'method' => 'post',
            'uri' => '/message/message-set-read',
            'params' => ['id'=>5],
        ];

        #删除通知
        $params['message_delete'] = [
            'method' => 'post',
            'uri' => '/message/message-delete',
            'params' => ['id'=>7],
        ];

        return $params;
    }

    // 通知列表 type = 2
    public function testNoticeList()
    {
        $this->apiTest($this->params['notice_list']);
    }

    // 公告列表 type = 1 不用登录
    public function testAnnounceList(){
        $this->apiTest($this->params['announce_list']);
    }

    // 未读通知
    public function testUnreadNumber(){
        $this->apiTest($this->params['unread_number']);
    }

    // 设置通知已读
    public function testRead(){
        /*$set_read = $this->params['set_read'];
        $this->assertEquals(1, $set_read['params']['id']);*/
        $this->apiTest($this->params['set_read']);
    }

    // 删除通知
    public function testMessageDelete(){
        /*$set_read = $this->params['message_delete'];
        $this->assertEquals(1, $set_read['params']['id']);*/
        $this->apiTest($this->params['message_delete']);
    }


}