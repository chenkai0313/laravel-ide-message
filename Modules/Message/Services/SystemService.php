<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/10/11
 * Time: 17:49
 */

namespace Modules\Message\Services;

use Illuminate\Support\Facades\Redis;

class SystemService
{
    /**
     * 推送公共信息redis存储
     */
    public function pushCommonDataRedis(){
        $redis = Redis::connection('default-d');
        $redis->set('name', 'Taylor');
        $user = $redis->get('name');

        // 推送基础数据
        $message_type = [
            'message_announcement' => '0',  // 群发公告
            'user_apply'   => '1',          // 审核-身份认证
            'active_white' => '2',          // 激活白条
            'order_status' => '3',          // 订单状态
            'repayment_reminder'  => '4',   // 还款提醒
            'collection_reminder' => '5',   // 催收提醒
            'credit_score' => '6',          // 信用积分
            'sms_send' => '7',              // 短信、验证码发送
        ];
        $redis->hmset("message-type",$message_type);

        $message_type_statement = [
            '0' => '群发公告',
            '1' => '审核-身份认证',
            '2' => '激活白条',
            '3' => '订单状态',
            '4' => '还款提醒',
            '5' => '催收提醒',
            '6' => '信用积分',
            '7' => '短信、验证码发送',
        ];
        $redis->hmset("message-type-statement",$message_type_statement);

        $type = $redis->hget('message-type','user_apply');
        $statement = $redis->hget('message-type-statement',$type);


        // 短信基础数据
        // 对照模板
        $sms_template = [
            'aliyun' => [
                'overdue_payment' => 'SMS_86665076',    // 逾期催款
                'willbe_overdue' => 'SMS_86565063',     // 将逾期催款
                'register_success' => 'SMS_86525063',   // 注册成功
                'bill_reminders' => 'SMS_86505066',     // 账单提醒
                'success_identity' => 'SMS_86525063',   // 认证成功
                'reimburse_money' => 'SMS_86685060',    // 退款
                'order_success' => 'SMS_86565058',      // 订单支付成功
                'order_dept' => 'SMS_86705057',         // 订单催款
                'security_code' => 'SMS_86755055',      // 验证码
            ],
        ];

        foreach ($sms_template as $key=>$value){
            $hmset_key = "sms-template-".$key;
            $redis->hmset($hmset_key,$sms_template[$key]);
        }




        $return = [
            'user' => $user,
            'type' => $type,
            'statement' => $statement,
        ];

        return $return;
    }
}