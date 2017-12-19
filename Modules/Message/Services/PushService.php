<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/12
 * Time: 14:55
 */

namespace Modules\Message\Services;

use Carbon\Carbon;
use App\Jobs\SendMessagePush; // 推送整合
use App\Jobs\SendScheduleJPush; // 失败补发
use Modules\Message\Models\MessagePush;
use Modules\Message\Models\MessageNotice;

class PushService
{
    // 1公告 2通知 3不需要显示的消息
    protected $type;

    // 队列选择
    protected $queue_status = true;

    // 推送队列名设置
    protected $redis_queue_key;

    /**
     * 通知 消息子类型：流程中推送消息[订单 激活白条 身份认证 还款提醒 催收提醒]
     */
    protected $message_type = [
        'message_announcement' => '0',  // 群发公告
        'user_apply'   => '1',          // 审核-身份认证
        'active_white' => '2',          // 激活白条
        'order_status' => '3',          // 订单状态
        'repayment_reminder'  => '4',   // 还款提醒
        'collection_reminder' => '5',   // 催收提醒
        'credit_score' => '6',          // 信用积分
        'sms_send' => '7',              // 短信、验证码发送
    ];

    // 对应状态说明
    protected $message_type_statement = [
        '0' => '群发公告',
        '1' => '审核-身份认证',
        '2' => '激活白条',
        '3' => '订单状态',
        '4' => '还款提醒',
        '5' => '催收提醒',
        '6' => '信用积分',
        '7' => '短信、验证码发送',
    ];

    // 发送类型 1仅推送 2短消息 3推送and消息 string  $operate_type
    protected $operate_type;

    // 消息发送状态 10230-10239
    protected $send_code = false;

    // 消息发送结果说明
    protected $send_msg = '发送失败';

    // 更新发送状态 true 新增  false 更新
    protected $message_code = true;

    protected $return_msg = [];
    /**
     * 推送接口
     * @param $type    int             推送类型：1公告，2通知
     * @param $message_type int        消息类型：0群发公告，1审核..
     * 消息类型
    'message_announcement' => '0',  // 群发公告
    'user_apply'   => '1',          // 审核-身份认证
    'active_white' => '2',          // 激活白条
    'order_status' => '3',          // 订单状态
    'repayment_reminder'  => '4',   // 还款提醒
    'collection_reminder' => '5',   // 催收提醒
    'credit_score' => '6',          // 信用积分
     * @param $operate_type int     	操作类型：1推送，2短消息，3推送+短消息
     * @param $audience     int         发送对象：0全部all，1个人regist_id
     * @param $title        string      标题
     * @param $description  string/array    消息内容
     * @param $tag          string      标签标识 非必填
     * @param $send_time    string      定时发送时间 非必填
     * @param $merge_regis  array       附带必填参数 发送对象信息
    merge_regis = [
    [
    user_id =>  // $user_id    int/array   目标对象ID：0公告，user_id
    registration_id => 'jpush_token' // $registration_id  string  设备唯一标识 jpush_token
    ]
    ]
     * @params $result  array           业务处理返回结果 ['code'=>1,'msg'=>'...'] 发送短消息时必填
    result = [
    code => 1, 跟移动端商量的参数 便于业务处理 例 code=1为审核通过 code=0为审核不通过
    msg => '...' 返回结果信息
    ]
     * @params $queue_status string     选择队列推送?(1:0) == true:false 非必填
     * @return array|mixed
     *
     * @author  yefan
     */
    public function send($params){
        $data = message_params_filter("push",$params);
        //file_put_contents_log("push",$data);
        $validator = \Validator::make(
            $data,
            \Config::get('validator.message.push.push-send'),
            \Config::get('validator.message.push.push-key'),
            \Config::get('validator.message.push.push-val')
        );
        if (!$validator->passes()) {
            return ['code' => 90002, 'msg' => $validator->messages()->first()];
        }

        if (!array_key_exists($data['message_type'], $this->message_type)) {
            return ["code" => 90002, 'msg' => '错误的消息子类型'];
        }

        $data = $this->dealParams($data);

        foreach($data as $key=>$value) {
            // 定时任务-直接发送
            if($data[$key]['is_direct'] == 1){
                $this->pushJobSend($data[$key]);  // 接入消息发送
                $this->send_code = true;
                $this->send_msg = "成功进入推送队列";
            }else{
                // 存储推送结果-定时
                $data[$key]['status'] = 0;
                \MessageService::table_push($data[$key]);     // 推送信息
                \MessageService::table_notice($data[$key]);   // 短消息信息
            }
        }

        //common_log('push','消息服务传参',$params,[]);
        return ['code'=>1,'msg'=>'极光验证通过','data'=>$data];
        //return ['code'=>1,'msg'=>'推送队列处理中'];
    }

    /*private function checkParams(){

    }*/

    // 处理传参数据
    private function dealParams($params){
        // 剔除整合参数
        if ($params['send_object'] == 'all' && !isset($params['object_info'])) {
            $params['object_info'] = [
                ['user_id' => 0]
            ];
        }
        if (!isset($params['result'])) {
            $params['result'] = ['code' => 1];
        }

        if (isset($params['send_time']) && strtotime($params['send_time']) > time()) {
            $params['is_direct'] = 0;
        } else {
            $params['is_direct'] = 1;
            $params['send_time'] = date('Y-m-d H:i:s');
        }

        if(isset($params['group_tags']) && $params['send_object'] != 'tags'){
            unset($params['group_tags']);
        }

        $queue_config = \Config::get('queue');
        $params['redis_queue_key'] = 'push-' . $queue_config['queue_pre'];

        $params['alert'] = $params['send_object'] == 'all'?$params['title']:$params['content'];
        if(!isset($params['extra'])){
            $params['extra'] = [
                'type' => $params['type'],
                'message_type' => $params['message_type'],
                'data' => $params['result'],
                'title' => $params['title'],
                'description' => $params['content'],
                'content' => $params['content'],
            ];
        }

        // 整理标准推送数据 array
        $push_list = [];
        if(isset($params['object_info'])){
            $object_info = $params['object_info'];
            unset($params['object_info']);
            foreach ($object_info as $key=>$value){
                $push_list[] = $object_info[$key];
            }
        }

        foreach($push_list as $key=>$value){
            foreach ($push_list[$key] as $k=>$v){
                if($k != 'user_id'){
                    $params['device_token'][$k] = $v; // 设备标识集合
                    unset($push_list[$key][$k]);
                }else{
                    $params[$k] = $v;
                }
            }
            $push_list[$key] = array_merge($push_list[$key],$params);
            ksort($push_list[$key]);
        }

        return $push_list;
    }

    /**
     * 推送队列发送
     */
    public function pushJobSend($params){
        $redis_queue_key = $params['redis_queue_key'];
        if( isset($params['delay']) ){
            $delay = $params['delay'];unset($params['delay']);
            $job = (new SendMessagePush($params))
                ->delay(Carbon::now()->addMinutes($delay))->onQueue($redis_queue_key);
        }else{
            $job = (new SendMessagePush($params))->onQueue($redis_queue_key);
        }
        dispatch($job);
    }
}