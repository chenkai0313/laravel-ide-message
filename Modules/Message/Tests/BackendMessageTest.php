<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/21
 * Time: 14:42
 */

namespace Modules\Message\Tests;

use Modules\Message\Tests\Common\MessageTestCase;

class BackendMessageTest extends MessageTestCase
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

        /*--------------------------------后台用接口-------------------------------------*/
        #推送列表
        $params['push_list'] = [
            'method' => 'post',
            'uri' => '/message/push-list',
            'params' => [
                // 'keyword' => '通知'
            ],
        ];

        #消息列表-公告
        $params['announce_list'] = [
            'method' => 'post',
            'uri' => '/message/message-announcelist-backend',
            'params' => [
                // 'keyword' => '通知'
            ],
        ];

        #消息列表-通知
        $params['notice_list'] = [
            'method' => 'post',
            'uri' => '/message/message-noticelist-backend',
            'params' => [
                // 'keyword' => '通知'
            ],
        ];

        #公告置顶
        $params['message_announcetop'] = [
            'method' => 'post',
            'uri' => '/message/message-announce-top',
            'params' => [
                'id' => '1'
            ],
        ];

        #推送
        $params['push_data'] = [
            'method' => 'post',
            'uri' => '/message/message-push',
            'params' => [
                'type' => 2,                    // 主消息类型 1公告 2通知
                'message_type' => 'repayment_reminder',     // 子消息类型 0 message_announcement 群发公告 1 user_apply 审核 身份认证 2 active_white 激活白条 3 order_status 订单状态 4 repayment_reminder 还款提醒 5 collection_reminder 催收提醒
                'title' => '测试标题-'.time(),  // 标题
                'description' => '测试内容-测试、测试、测试',  // 内容描述
                'audience' => 'regis_id',       // 发送对象 所有人all 个人regis_id
                'operate_type' => 3,            // 操作类型 1仅推送 2短消息 3推送and消息
                'user_id' => 1,                 // user_id = 1 为测试主账号
                'registration_id' => '101d8559097d3442cc9'     // 唯一设备token
            ],
        ];

        return $params;
    }

    /**
     * 推送列表
     * @Author yefan
     */
    public function testPushList()
    {
        $this->apiTest($this->params['push_list']);
    }

    // 消息列表-公告
    public function testAnnounceList()
    {
        $this->apiTest($this->params['announce_list']);
    }

    // 消息列表-通知
    public function testNoticeList()
    {
        $this->apiTest($this->params['notice_list']);
    }

    // 公告置顶  临时 无法回滚message项目数据
    public function testMessageTop(){
        $set_read = $this->params['message_announcetop'];
        // $this->assertEquals(1, $set_read['params']['id']);
        $this->apiTest($this->params['message_announcetop']);
    }

    // 推送操作
    public function testPushOperate(){
        $this->apiTest($this->params['push_data']);
    }


}