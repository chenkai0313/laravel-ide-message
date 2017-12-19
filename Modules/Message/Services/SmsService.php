<?php
/**
 * Created by PhpStorm.
 * User: pc06
 * Date: 2017/9/1
 * Time: 9:46
 */

namespace Modules\Message\Services;

use Carbon\Carbon;
use App\Jobs\SendMessageSms;
use Overtrue\EasySms\EasySms;
use Modules\Message\Models\MessageSms;

class SmsService
{
    protected $open_code = true; // 发送开启状态 true false

    protected $gateways = 'aliyun'; // 网关选择

    // 对照模板
    protected $send_template = [
        'aliyun' => [
            'overdue_payment' => 'SMS_86665076',    // 逾期催款
            'willbe_overdue' => 'SMS_86565063',     // 将逾期催款
            'register_success' => 'SMS_86525063',   // 注册成功
            'bill_reminders' => 'SMS_86505066',     // 账单提醒
            'success_identity' => 'SMS_86520063',   // 认证成功
            'reimburse_money' => 'SMS_86685060',    // 退款
            'order_success' => 'SMS_86565058',      // 订单支付成功
            'order_dept' => 'SMS_86705057',         // 订单催款
            'security_code' => 'SMS_86755055',      // 验证码
        ],
    ];

    /**
     * content 内容
     * template 模板标识
     * mobile 手机号码
     */
    public function sendEntry($params){
        $gateways_list = $this->send_template[$this->gateways];
        if( isset($gateways_list[$params['send_template']]) ){
            $params['send_template'] = $gateways_list[$params['send_template']];
        }else{
            \Log::info('没有该模板',$params);
            return true;
        }
        // 短信发送开发
        $this->open_code = \Config::get('services.sms.sms_send_entry');
        // 处理获取数据
        foreach ($params as $key=>$value) {
            if($key == 'data'){
                $params['data'] = json_decode($params['data'],true);
            }
            if($key == 'mobile'){
                $params['mobile'] = json_decode($params['mobile'],true);
            }
        }
        // 发送
        if(is_array($params['mobile'])){
            $mobile = $params['mobile'];
            unset($params['mobile']);
            $sms_list = [];
            foreach ($mobile as $key=>$value){
                $params['mobile'] = $mobile[$key];
                $sms_list[] = $params;
            }
            if(!empty($sms_list)){
                foreach ($sms_list as $value){
                    $this->noqueueSend($value);
                }
            }
            unset($sms_list);
            unset($mobile);
        }else{
            $this->noqueueSend($params);
        }

        return ['code'=>1,'msg'=>'短信发送成功'];
    }

    // 发送短信-不进队列
    private function noqueueSend($params){
        $Config = \Config::get('sms');
        $easySmsSend = new EasySms($Config);
        try {
            if ($this->open_code) {
                $easySmsSend->send($params['mobile'], [
                    'content' => $params['content'],
                    'template' => $params['send_template'],
                    'data' => $params['data'],
                ]);
                $params['status'] = 1;
            } else {
                $params['status'] = 0;
            }
        } catch (\Exception $e) {
            $e->getMessage();
            $params['status'] = 2;
            common_log('sms','短信间隔时间太短或超出发送上限，发送失败',$params, (array)$e ,'error');
            // return ['code' => 500, 'msg' => '短信间隔时间太短或超出发送上限，发送失败'];
        }
        $this->send_record($params);
        return true;
    }

    // 记录短信发送记录
    public function send_record($params){
        $sms_data = [
            'send_template' => $params['send_template'],     // 模板标识
            'phone' => $params['mobile'],
            'content' => $params['content'],
            'data' => json_encode($params['data']), // 模板替换标签
            'status' => $params['status'],          // 发送状态（0未发送/1已发送/2失败）
        ];
        if(isset($params['send_time'])){
            $sms_data['send_time'] = $params['send_time'];
        }
        return MessageSms::smsRecordAdd($sms_data);
    }

    // 队列发送入口
    public function queueSendEntry($params){
        $gateways_list = $this->send_template[$this->gateways];
        if( isset($gateways_list[$params['send_template']]) ){
            $params['send_template'] = $gateways_list[$params['send_template']];
        }else{
            \Log::info('没有该模板',$params);
            return true;
        }
        // 处理获取数据
        foreach ($params as $key=>$value) {
            if($key == 'data'){
                $params['data'] = json_decode($params['data'],true);
            }
            if($key == 'mobile'){
                $params['mobile'] = json_decode($params['mobile'],true);
            }
        }

        if(isset($params['mobile'])){
            $mobile = $params['mobile'];
            unset($params['mobile']);
            $sms_list = [];
            foreach ($mobile as $key=>$value){
                $params['mobile'] = $mobile[$key];
                $sms_list[] = $params;
            }
            if(!empty($sms_list)){
                $sms_list['array_status'] = true; // 多条数据
                $this->queueSend($sms_list);
            }
            unset($sms_list);
            unset($mobile);
        }else{
            $this->queueSend($params);
        }

        return ['code' => 1, 'msg' => '短信处理中'];
    }

    // 队列发送
    private function queueSend($params){
        $Config = \Config::get('queue');
        if( isset($params['delay']) ){
            $delay = $params['delay'];
            unset($params['delay']);
            $job = (new SendMessageSms($params))
                ->delay(Carbon::now()->addMinutes($delay))->onQueue('sms-'.$Config['queue_pre']);
        }else{
            $job = (new SendMessageSms($params))->onQueue('sms-'.$Config['queue_pre']);
        }

        dispatch($job);
    }

    public function smsList($params){
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        if(isset($params['keyword'])){
            if(is_numeric($params['keyword'])){
                $params['phone'] = $params['keyword'];
            }
            $params['content'] = $params['keyword'];
            unset($params['keyword']);
        }
        $res = MessageSms::smsList($params);
        foreach ($res as $key => $value ) {
            $res[$key]['status'] = $res[$key]['status'] == 1?'已发送':'未发送';
        }
        $count = MessageSms::smsCount($params);
        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);
        return ['code' => 1, 'data' => $result];
    }

    /*-------------------------------------------------------------------*/
    public function send($params){
        $data = message_params_filter("sms",$params);
        //file_put_contents_log("sms",$data);
        $validator = \Validator::make(
            $data,
            \Config::get('validator.message.sms.sms-send'),
            \Config::get('validator.message.sms.sms-key'),
            \Config::get('validator.message.sms.sms-val')
        );
        if (!$validator->passes()) {
            return ['code' => 90002, 'msg' => $validator->messages()->first()];
        }
        // 验证手机号码
        $mobile_error = true;
        foreach ($data['user_mobile'] as $key=>$value) {
            if(!preg_match ("/^1[34578]+\d{9}$/", $value)){
                $mobile_error = false; break;
            }
        }
        if (!$mobile_error){
            return ['code' => 90002, 'msg' => "手机号格式不正确"];
        }
        // 模板替换
        $gateways_list = $this->send_template[$this->gateways];
        if( isset($gateways_list[$data['send_template']]) ){
            $data['send_template'] = $gateways_list[$data['send_template']];
        }else{
            \Log::info('没有该模板',$data);
            return ['code' => 90002, 'msg' => "消息服务没有对应模板"];
        }

        $data = $this->dealParams($data);

        // 发送短信
        $this->queueSend($data);

        // common_log('sms','消息服务传参',$params,[]);
        // return ['code'=>1,'msg'=>'短息队列处理中','data'=>$data];
        return ['code'=>1,'msg'=>'短息队列处理中'];
    }

    /**
     * 处理传参数据
     * user_mobile 替换成 mobile
     */
    private function dealParams($params){
        $sms_list = [];
        if(isset($params['user_mobile'])){
            $mobile = $params['user_mobile'];unset($params['user_mobile']);
            foreach ($mobile as $key=>$value){
                $params['mobile'] = $mobile[$key];
                $sms_list[] = $params;
            }
            if(!empty($sms_list)){
                $sms_list['array_status'] = true; // 多条数据
            }
            unset($mobile);
        }else{
            $sms_list[] = $params;
        }

        return $sms_list;
    }
}