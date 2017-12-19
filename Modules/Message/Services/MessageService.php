<?php
/**
 * Created by PhpStorm.
 * User: 叶帆
 * Date: 2017/8/21
 * Time: 13:59
 */

namespace Modules\Message\Services;

use Carbon\Carbon;
use App\Jobs\SendMessageJPush; // 第一次发送
use App\Jobs\SendScheduleJPush; // 失败补发
use Modules\Message\Models\MessagePush;
use Modules\Message\Models\MessageNotice;

use Exception;

class MessageService
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
     * 消息发送处理入口 - 参数构建
     * @param $params ['operate_type'] 发送类型 1仅推送 2短消息 3推送and消息 string
     * @param $params ['type'] 消息类型 $this->message_type
     * @param $params ['is_direct'] 是否直接发送：0定时发送，1直接发送
     * @param $params ['send_time'] 发送时间
     */
    public function sendEntry($params){
        /*$validator = \Validator::make(
            $params,
            \Config::get('validator.message.push.push-send'),
            \Config::get('validator.message.push.push-key'),
            \Config::get('validator.message.push.push-val')
        );
        if (!$validator->passes()) {
            return ['code' => 90002, 'msg' => $validator->messages()->first()];
        }*/

        if(!in_array($params['type'],[1,2,3])){
            return ["code" => 10231, 'msg' => '错误的消息主类型'];
        }
        if(!array_key_exists($params['message_type'],$this->message_type)){
            return ["code" => 10231, 'msg' => '错误的消息子类型'];
        }
        if( !in_array($params['operate_type'],[1,2,3]) ){
            return ["code" => 10231, 'msg' => '错误发送操作类型、请填写正确'];
        }
        $this->operate_type = $params['operate_type'];unset($params['operate_type']);
        if($params['audience'] == 'tags'){
            if(!isset($params['tags'])){
                return ["code" => 10231, 'msg' => '缺少标签信息'];
            }
        }else if($params['audience'] == 'regis_id' || $params['audience'] == 'alias'){
            if(!isset($params['merge_regis'])){
            }else{
                $params['merge_regis'] = json_decode($params['merge_regis'],true); // 多用户数据整理
            }
        }
        // 区分定时任务 is_direct 0 定时 status 0 未处理
        if( isset($params['send_time']) && strtotime($params['send_time'])>time() ){
            $params['is_direct'] = 0;
            $params['status'] = 0;
        }else{
            $params['is_direct'] = 1;
            $params['send_time'] = date('Y-m-d H:i:s');
        }

        if(!isset($params['result'])){
            $params['result'] = ['code' => 1];
        }else{
            $params['result'] = json_decode($params['result'],true);
        }
        $params['result']['title'] = $params['title'];
        $params['result']['description'] = $params['description'];

        // 选择队列推送?true:false(1:0)
        if( isset($params['queue_status']) && $params['queue_status'] == 0){
            $this->queue_status = false;
            unset($params['queue_status']);
        }else{
            $queue_config = \Config::get('queue');
            $this->redis_queue_key = 'push-'.$queue_config['queue_pre'];
        }

        $this->messageSend($params);  // 接入消息发送
        if($this->send_code){
            return ["code" => 1, 'msg' => $this->send_msg ];
        }else{
            return ["code" => 10230, 'msg' => $this->send_msg ];
        }
    }

    /**
     * 推送与短消息
     * @param $params ['user_id'] 用户id == alias
     * @param $params ['title'] 标题
     * @param $params ['result'] 返回结果
     * @param $params ['type'] 类型
     * 具体格式
     * $data = [
        'alias' => $params['alias'],
        'audience' => $params['audience'],
        'platform' => $params['platform'],
        'registration_id' => $params['registration_id'],
        'tags' => $params['tags'],
        'title' => $params['title'],
        'description' => $params['description'],
        'extra' => [
            'type' => $params['type'],
            'data' => $params['result'],
        ]
     ];
     */
    public function messageSend($params){
        // 整理推送数据
        $params['alert'] = $params['audience'] == 'all'?$params['title']:$params['description'];
        if(!isset($params['extra'])){
            $params['extra'] = [
                'type' => $params['type'],
                'message_type' => $params['message_type'],
                'data' => $params['result'],
                'title' => $params['title'],
                'description' => $params['description'],
            ];
        }
        $params['operate_type'] = $this->operate_type;

        $push_list = [];
        if(isset($params['merge_regis'])&&$params['type']==2){
            $merge_regis = $params['merge_regis'];
            unset($params['merge_regis']);
            foreach ($merge_regis as $key=>$value){
                $push_list[] = $merge_regis[$key];
            }
        }else{
            $push_list[] = $params;
        }

        // 循环处理推送
        foreach($push_list as $key=>$value){
            array_merge($params,$push_list[$key]);
            $params['alias'] = $push_list[$key]['user_id'];

            // 定时任务-直接发送
            if($params['is_direct'] == 1){
                if(!$this->queue_status){
                    // 调用推送接口
                    $push_return = false;
                    if($this->operate_type == 1){
                        $push_return = \jpush::pushSend($params);
                    }
                    if($this->operate_type == 2){
                        $push_return = \jpush::messageSend($params);
                    }
                    if($this->operate_type == 3){
                        $push_return = \jpush::jpushSend($params);
                    }
                    // 处理推送结果
                    if(isset($push_return['http_code']) && $push_return['http_code'] == 200){
                        $this->send_code = true;
                        $this->return_msg = $push_return;
                        $this->send_msg = '极光推送成功';
                        common_log('jupsh','极光推送结果',$params,(array)$push_return,'info');
                        $params['push_return'] = $push_return;
                    }else{
                        $this->send_msg = "极光服务器推送失败";
                        common_log('jupsh','极光推送失败',$params,(array)$push_return,'error');
                    }

                    $params['msg_id'] = $this->send_code?$push_return['body']['msg_id']:'';
                }else{
                    // 直接发送 进队列 return true
                    if( isset($params['delay']) ){
                        $delay = $params['delay'];
                        unset($params['delay']);
                        $job = (new SendMessageJPush($params))
                            ->delay(Carbon::now()->addMinutes($delay))->onQueue($this->redis_queue_key);
                    }else{
                        $job = (new SendMessageJPush($params))->onQueue($this->redis_queue_key);
                    }
                    dispatch($job);
                }
            }

            // 存储推送结果
            if(!$this->queue_status){
                $this->table_push($params);     // 推送信息
                $this->table_notice($params);   // 短消息信息
            }else{
                $this->send_code = true;
                $this->send_msg = "成功进入推送队列";
                continue;
            }
        }

        return true;
    }

    /**
     * 推送表记录
     * 发送http_code为200才记录表 $this->send_msg 为true
     * @param $params ['is_direct'] 是否直接发送：0定时发送，1直接发送
     * @param $params ['send_time'] 发送时间
     */
    public function table_push($params){
        if( isset($params['operate_type']) ){
            if( $params['operate_type'] == 2 ){
                return true;
            }
        }else if($this->operate_type == 2 ){
            return true;
        }
        $params_push['msg_id'] = isset($params['msg_id'])?$params['msg_id']:'';
        $params_push['type'] = $params['type'];
        $params_push['message_type'] = $this->message_type[$params['message_type']];
        $params_push['platform'] = isset($params['platform'])?$params['platform']:["android", "ios"];
        $params_push['platform'] = json_encode($params_push['platform']);
        $params_push['send_type'] = isset($params['send_object'])?$params['send_object']:$params['audience'];
        $params_push['user_id'] = $params['user_id'];
        $params_push['title'] = $params['title'];
        $params_push['content'] = isset($params['description'])?$params['description']:$params['content'];
        $params_push['extra'] = json_encode($params);
        $params_push['is_direct'] = $params['is_direct'];
        $params_push['send_time'] = $params['is_direct']==1?date('Y-m-d H:i:s'):$params['send_time'];
        $params_push['status'] = isset($params['status'])?$params['status']:($this->send_code?1:2);

        return MessagePush::MessagePushAdd($params_push);
    }

    /**
     * 短消息表记录
     * 发送http_code为200才记录表 $this->send_msg 为true
     * @param $params ['is_direct'] 是否直接发送：0定时发送，1直接发送
     * @param $params ['send_time'] 发送时间
     */
    public function table_notice($params){
        if( isset($params['operate_type']) ){
            if( $params['operate_type'] == 1 ){
                return true;
            }
        }else if($this->operate_type == 1 ){
            return true;
        }
        $params_notice['msg_id'] = isset($params['msg_id'])?$params['msg_id']:'';
        $params_notice['type'] = $params['type'];
        $params_notice['message_type'] = $this->message_type[$params['message_type']];
        $params_notice['platform'] = isset($params['platform'])?$params['platform']:["android", "ios"];
        $params_notice['platform'] = json_encode($params_notice['platform']);
        $params_notice['send_type'] = isset($params['send_object'])?$params['send_object']:$params['audience'];
        $params_notice['user_id'] = $params['user_id'];
        $params_notice['title'] = $params['title'];
        $params_notice['content'] = isset($params['description'])?$params['description']:$params['content'];
        $params_notice['extra'] = json_encode($params);
        $params_notice['is_direct'] = $params['is_direct'];
        $params_notice['send_time'] = $params['is_direct']==1?date('Y-m-d H:i:s'):$params['send_time'];
        $params_notice['status'] = isset($params['status'])?$params['status']:($this->send_code?1:2);

        return MessageNotice::MessageNoticeAdd($params_notice);
    }

    /**
     * 消息类型状态获取
     */
    protected function check_message_type($message_type){
        $return = false;
        if($message_type === 0 || is_numeric($message_type)){
            $types = $this->message_type;
        }else{
            $types = $this->message_type_statement;
        }
        foreach ($types  as $key=>$value) {
            if($types[$key] == $message_type){
                $return = ['message_type' => $key];
                break;
            }
        }

        return $return;
    }

    /**
     * 短消息列表-all
     */
    public function messageList($params){
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        $res = MessageNotice::MessageList($params);
        foreach($res as $key=>$value){
            if(isset($res[$key]['extra'])){
                $res[$key]['extra'] = json_decode(stripslashes($res[$key]['extra']),true);
            }
        }
        $count = MessageNotice::MessageListCount($params);
        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);

        return ['code' => 1, 'msg' => '消息获取成功','data'=>$result];

    }

    /**
     * 短消息列表-通知-会员用户
     * @params message_type 消息类型
     * @params user_id 用户id  通过用户登录token拿到
     */
    public function messageNotice($params){
        if(isset($params['type'])){
            if($params['type'] != 2){
                return ["code" => 10231, 'msg' => '错误的消息主类型'];
            }
        }else{
            $params['type'] = 2;
        }
        if ( !isset($params['user_id']) ) {
            // $params['user_id'] = get_user_id();
            return ["code" => 10231, 'msg' => '获取会员通知信息必填user_id'];
        }
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        $res = MessageNotice::MessageNoticeList($params);
        foreach($res as $key=>$value){
            $res[$key]['message_time'] = date("m/d H:i",strtotime($res[$key]['send_time']));
            unset($res[$key]['msg_id']);
            unset($res[$key]['user_id']);
            unset($res[$key]['extra']);
        }
        $count = MessageNotice::MessageCount($params);
        $number = MessageNotice::messageCountUnRead($params);

        $result['list'] = $res;
        $result['total'] = $count;
        $result['number'] = $number;
        $result['pages'] = ceil($count/$params['limit']);
        return ['code' => 1, 'data' => $result];
    }

    /**
     * 短消息列表-公告
     * @params message_type 消息类型
     * @params user_id 用户id  通过用户登录token拿到
     */
    public function  messageAnnounce($params){
        if(isset($params['type'])){
            if($params['type'] != 1){
                return ["code" => 10231, 'msg' => '错误的消息主类型'];
            }
        }else{
            $params['type'] = 1;
        }

        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        $res = MessageNotice::MessageAnnounceList($params);
        foreach($res as $key=>$value){
            $res[$key]['message_time'] = date("m/d H:i",strtotime($res[$key]['send_time']));
            unset($res[$key]['msg_id']);
            unset($res[$key]['user_id']);
            unset($res[$key]['is_read']);
            unset($res[$key]['extra']);
        }
        $count = MessageNotice::MessageCount($params);

        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);

        return ['code' => 1, 'data' => $result];
    }

    /**
     * 推送列表-push-后台
     * @param $params ['keyword'] 后台列条搜索条件
     */
    public function messagePush($params){
        // 搜索条件整合
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        if(isset($params['keyword'])){
            $message_type = $this->check_message_type($params['keyword']);
            if($message_type){
                $params['message_type'] = $message_type['message_type'];
            }else{
                $params['title'] = $params['keyword'];
            }
            unset($params['keyword']);
        }

        $res = MessagePush::MessagePushList($params);
        foreach($res as $key=>$value){
            unset($res[$key]['extra']);
            unset($res[$key]['msg_id']);
            if(isset($res[$key]['platform'])){
                $platform = json_decode(stripslashes($res[$key]['platform']),true);
                $res[$key]['platform'] = implode("、", $platform);
            }
            switch($res[$key]['type']){
                case 1:
                    $res[$key]['type'] = '公告';break;
                case 2:
                    $res[$key]['type'] = '通知';break;
                default:break;
            }
            $res[$key]['message_type'] = $this->message_type_statement[$res[$key]['message_type']];
            switch($res[$key]['status']){
                case 0:
                    $res[$key]['status'] = '未发送';break;
                case 1:
                    $res[$key]['status'] = '发送成功';break;
                case 2:
                    $res[$key]['status'] = '发送失败';break;
                default:
                    $res[$key]['status'] = '未知状态';break;
            }
        }
        $count = MessagePush::MessagePushCount($params);
        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);

        return ['code' => 1, 'msg' => '推送信息获取成功','data'=>$result];
    }

    /**
     * 短消息列表-通知-后台
     * @param $params ['keyword'] 后台列条搜索条件 message_type 消息类型 title 标题
     */
    public function messageNoticeBackend($params){
        $params['type'] = 2;
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        if(isset($params['keyword'])){
            $message_type = $this->check_message_type($params['keyword']);
            if($message_type){
                $params['message_type'] = $message_type['message_type'];
            }else{
                $params['title'] = $params['keyword'];
            }
            unset($params['keyword']);
        }
        $params['type'] = 2; // 消息主类型
        $res = MessageNotice::MessageNoticeListBackend($params);
        foreach($res as $key=>$value){
            unset($res[$key]['msg_id']);
            unset($res[$key]['type']);
            unset($res[$key]['extra']);
            $res[$key]['is_read'] = $res[$key]['is_read']== 1 ? '已读':'未读';
            $res[$key]['message_type'] = $this->message_type_statement[$res[$key]['message_type']];
            switch($res[$key]['status']){
                case 0:
                    $res[$key]['status'] = '未发送';break;
                case 1:
                    $res[$key]['status'] = '发送成功';break;
                case 2:
                    $res[$key]['status'] = '发送失败';break;
                default:
                    $res[$key]['status'] = '未知状态';break;
            }
        }
        $count = MessageNotice::MessageListCount($params);

        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);
        return ['code' => 1, 'data' => $result];
    }

    /**
     * 短消息列表-公告-后台
     * @param $params ['keyword'] 后台列条搜索条件 message_type 消息类型 title 标题
     */
    public function messageAnnounceBackend($params){
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 20;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        if(isset($params['keyword'])){
            $message_type = $this->check_message_type($params['keyword']);
            if($message_type){
                $params['message_type'] = $message_type['message_type'];
            }else{
                $params['title'] = $params['keyword'];
            }
            unset($params['keyword']);
        }
        $params['type'] = 1; // 消息主类型
        $res = MessageNotice::MessageAnnounceListBackend($params);
        foreach($res as $key=>$value){
            unset($res[$key]['msg_id']);
            unset($res[$key]['user_id']);
            unset($res[$key]['is_read']);
            unset($res[$key]['type']);
            unset($res[$key]['extra']);
            $res[$key]['message_type'] = $this->message_type_statement[$res[$key]['message_type']];
            switch($res[$key]['status']){
                case 0:
                    $res[$key]['status'] = '未发送';break;
                case 1:
                    $res[$key]['status'] = '发送成功';break;
                case 2:
                    $res[$key]['status'] = '发送失败';break;
                default:
                    $res[$key]['status'] = '未知状态';break;
            }
        }
        $count = MessageNotice::MessageListCount($params);

        $result['list'] = $res;
        $result['total'] = $count;
        $result['pages'] = ceil($count/$params['limit']);
        return ['code' => 1, 'data' => $result];
    }

    /**
     * 设置消息已读
     * @params id 消息id string   '1,2,3,4,5,6'
     */
    public function messageSetRead($params){
        $data['is_read'] = 1;
        if( !is_array($params['id']) ){
            $data['id'] = explode(',',$params['id']);
        }
        $result = MessageNotice::messageSetRead($data);
        if($result){
            return ['code' => 1, 'msg' => '成功设置消息为已读'];
        }else{
            return ['code' => 10235, 'msg' => '设置消息已读失败'];
        }
    }

    /**
     * 删除消息
     * @params id 消息id
     */
    public function messageDelete($params){
        $result = MessageNotice::messageDelete($params);
        if($result){
            return ['code' => 1, 'msg' => '删除消息成功'];
        }else{
            return ['code' => 10236, 'msg' => '删除消息失败'];
        }
    }

    /**
     * 未读消息总数 - 公告不计数 - 接口调用封装
     * @params user_id 用户id
     */
    public function messageUnRead($params){
        if(!isset($params['user_id'])){
            $number = 0;
        }else{
            $result = MessageNotice::messageCountUnRead($params);
            $number = $result && $result > 0 ? $result : 0;
        }
        return ['code' => 1, 'data' => $number];
    }

    /**
     * 第一条公告提示 不需要登录
     */
    public function getFirstAnnouncement(){
        $topData = MessageNotice::messageTopData();
        return ['code' => 1, 'msg' => '获取成功', 'data' => $topData['content']];
    }

    /**
     * 消息置顶
     * @params id 消息id
     */
    public function messageTop($params){
        if(!isset($params['id'])){
            return ['code' => 10235, 'msg' => '缺少必要字段-id'];
        }
        $find = MessageNotice::find($params['id']);
        if(!$find || $find['type'] != 1){
            return ['code' => 10235, 'msg' => '不是可置顶公告'];
        }
        $topData = MessageNotice::messageTopData();
        if($topData['top'] != '0' && $topData['id'] == $params['id']){
            return ['code' => 1, 'msg' => '已经是最高置顶'];
        }else{
            if($topData['top'] >= 990){
                MessageNotice::messageTopClean();
                $top = 1;
            }else{
                $top = $topData['top']+1;
            }

            $data['top'] = $top;
            $data['id'] = $params['id'];
            $result = MessageNotice::messageEdit($data);
            if($result){
                return ['code' => 1, 'msg' => '成功置顶消息'];
            }else{
                return ['code' => 10235, 'msg' => '消息置顶失败'];
            }
        }
    }

    // PC公告接口
    /*public function pcAnnounce($params){
        $params['limit'] = isset($params['limit']) ? $params['limit'] : 5;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        $params['type'] = 1; // 消息主类型
        $res = MessageNotice::MessageAnnounceListPC($params);
        foreach($res as $key=>$value){
            unset($res[$key]['msg_id']);
            unset($res[$key]['type']);
            $res[$key]['message_type'] = $this->message_type_statement[$res[$key]['message_type']];
            switch($res[$key]['status']){
                case 0:
                    $res[$key]['status'] = '未发送';break;
                case 1:
                    $res[$key]['status'] = '发送成功';break;
                case 2:
                    $res[$key]['status'] = '发送失败';break;
                default:
                    $res[$key]['status'] = '未知状态';break;
            }
        }

        $result['list'] = $res;
        return ['code' => 1, 'data' => $result];
    }*/

    /*---------------------------------------------定时任务---------------------------------------------*/
    // 定时推送脚本
    public function scheduleMessage($params){
        // 选择不走队列
        if(isset($params['queue_status']) && $params['queue_status'] == 1){
            $this->queue_status = false;
        }
        $queue_config = \Config::get('queue');
        $this->redis_queue_key = 'push-schedule-'.$queue_config['queue_pre']; // 定时补发队列

        $condition = [
            'is_direct' => '0',
            'status' => '0',
            'send_time' => date('Y-m-d H:i:s'),
        ];
        $push = MessagePush::pushToSend($condition);
        $push_return = $this->scheduleParams($push,['message_sign'=>'push','operate_type'=>1]);
        $message = MessageNotice::messageToSend($condition);
        $message_return = $this->scheduleParams($message,['message_sign'=>'message','operate_type'=>2]);

        if($this->queue_status){
            return ['code' => 1, 'msg' => '成功进入定时推送队列'];
        }else{
            $success_number = $push_return['data']['success_number']+$message_return['data']['success_number'];
            $error_number = $push_return['data']['error_number']+$message_return['data']['error_number'];
            return ['code' => 1, 'msg' => '执行完成，成功数量：'.$success_number.'、失败数量：'.$error_number];
        }
    }

    /**
     * 参数整理
     * @params $message 推送信息 array
     * @params $object  区分短消息与推送数据 array
     */
    public function scheduleParams($message,$object){
        // 初始化推送数量
        $success_number = $error_number = 0;

        // 整理推送数据格式
        foreach ($message as $key=>$value) {
            $params = [
                'id' => $message[$key]['id'],
                'alias' => $message[$key]['user_id'],
                'audience' => $message[$key]['send_type'],  // 推送对象 广播(all)、regis_id、tags、alias  (目前分单推regis_id与广播all)
                'title' => $message[$key]['title'],         // 标题
                'description' => $message[$key]['content'], // 内容
                'message_sign' => $object['message_sign'],  // 推送功能类型 message 通知\公告 push 推送
                'operate_type' => $object['operate_type'],  // 操作类型
            ];
            if (isset($message[$key]['platform'])) {
                $message[$key]['platform'] = json_decode(stripslashes($message[$key]['platform']), true);
                $params['platform'] = $message[$key]['platform']; // 推送平台 array
            }
            $message[$key]['extra'] = json_decode(stripslashes($message[$key]['extra']), true);
            $params['extra'] = $message[$key]['extra'];
            $params['operate_type'] = isset($params['extra']['operate_type'])?$params['extra']['operate_type']:3;
            $this->operate_type = $params['operate_type'];

            $params['extra']['registration_id'] = '121c83f7601f7e2e9db';  // 临时数据
            if(isset($params['extra']['registration_id'])){
                $params['registration_id'] = $params['extra']['registration_id'];
            }else{
                common_log('jupsh','registration_id字段不存在',$params,[],'error');
                continue;
            }

            // $params['tags'] tags参数 后期添加
            if ($this->message_code) {
                $this->message_code = false; // 更新发送状态 true 新增  false 更新
            }

            // 接入消息发送
            $schedule_return = $this->scheduleSend($params);

            // 确认发送失败数量
            if(is_array($schedule_return)&&!is_bool($schedule_return)){
            }else{
                if(!$this->send_code){
                    $error_number++;
                }else{
                    $success_number++;
                }
            }
        }

        return ['code' => 1, 'msg' => '参数处理完成', 'data' => ["success_number" => $success_number, 'error_number' => $error_number]];
    }

    /**
     * 更新推送信息
     * @param $params ['message_sign'] 推送功能类型 push message
     */
    public function message_update($params){
        $data = [
            'status' => isset($params['status'])?$params['status']:($this->send_code?1:2),
            'id' => $params['id'],
            'msg_id' => isset($params['msg_id'])?$params['msg_id']:"",
        ];

        if($params['message_sign'] == 'push'){
            return MessagePush::pushEdit($data);
        }else{
            return MessageNotice::messageEdit($data);
        }
    }

    /**
     * 脚本推送
     * @param $params ['user_id'] 用户id
     * @param $params ['title'] 标题
     * @param $params ['result'] 返回结果
     * @param $params ['type'] 类型
     */
    public function scheduleSend($params){
        // 整理推送数据
        $params['alert'] = $params['audience'] == 'all'?$params['title']:$params['description'];
        if(!isset($params['extra'])){
            $params['extra'] = [
                'type' => $params['type'],
                'message_type' => $params['message_type'],
                'data' => $params['result'],
                'title' => $params['title'],
                'description' => $params['description'],
            ];
        }

        if(!$this->queue_status){
            // 调用推送接口
            $push_return = false;
            if($this->operate_type == 1){
                $push_return = \jpush::pushSend($params);
            }
            if($this->operate_type == 2){
                $push_return = \jpush::messageSend($params);
            }
            if($this->operate_type == 3){
                $push_return = \jpush::jpushSend($params);
            }

            // 处理推送结果
            if(isset($push_return['http_code']) && $push_return['http_code'] == 200){
                $this->send_code = true;
                $this->return_msg = $push_return;
                $params['push_return'] = $push_return;
                $this->send_msg = '极光推送成功';
            }else{
                $this->send_msg = "极光服务器推送失败";
                common_log('jupsh','极光推送\定时脚本推送失败',$params,(array)$push_return,'error');
            }
        }else{
            $params['operate_type'] = $this->operate_type;
            // 直接发送 进队列 return true
            $job = (new SendScheduleJPush($params))->onQueue($this->redis_queue_key);
            dispatch($job);

            return ['code' => 1, 'msg' => '成功推入推送定时队列（失败、补发等重发）'];
        }

        $data = [
            'status' => $this->send_code?1:2,
            'message_sign' => $params['message_sign'],
            'id' => $params['id'],
            'msg_id' => $this->send_code?$params['push_return']['body']['msg_id']:'',
        ];
        $this->message_update($data);

        return true;
    }




    /**
     * 推送服务商选择
     * $this->serviceProvider($params);
     */
    /*public function serviceProvider($params){
        $service = $params['device_token'];
        if($params['type'] == 1){
            $params['registration_id'] = "";
        }else{
            foreach ($service as $key=>$value){
                if($value == $params['registration_id']){

                }
            }
        }
    }*/


    /*------------------------------------------------------------------------*/

    public function messgaeSendEntry($params){
        $validator = \Validator::make(
            $params,
            \Config::get('validator.message.message.message-send'),
            \Config::get('validator.message.message.message-key'),
            \Config::get('validator.message.message.message-val')
        );
        if (!$validator->passes()) {
            return ['code' => 90002, 'msg' => $validator->messages()->first()];
        }

        // 消息服务选择
        switch (strtolower($params['services'])){
            case "push":
                $return = \PushService::send($params);
                break;
            case "sms":
                $return = \SmsService::send($params);
                break;
            case "smspush":
                // 推送
                $push_code = true;
                try{
                    \PushService::send($params);
                } catch (\Exception $e){
                    $push_code = false;
                }
                // 短信
                $sms_code = true;
                try{
                    \SmsService::send($params);
                } catch (\Exception $e){
                    $sms_code = false;
                }
                //return ['code' => 10231, 'msg' => '暂不支持同步发送'];
                return $this->bothReturn($push_code,$sms_code);
                break;
            default:
                return ['code' => 90002, 'msg' => '错误的消息服务对象'];
                break;
        }

        // 子服务需要验证数据格式 正确返回 布尔值的true 错误返回常规格式提示
        if(!is_bool($return)){
            return $return;
        }else{
            return ['code' => 1, 'msg' => '消息服务成功'];
        }
    }

    // 验证两者同时发送
    private function bothReturn($push,$sms){
        $numbers = 1;$msg="消息服务处理中";
        if(!$push){
            $numbers++;$msg="推送处理失败";
        }
        if(!$sms){
            $numbers++;$msg="短信处理失败";
        }

        switch ($numbers){
            case "1":
                return ['code' => 1, 'msg' => '消息服务短信、推送皆发送成功'];
                break;
            case "2":
                return ['code' => 1, 'msg' => $msg];
                break;
            case "3":
                return ['code' => 1, 'msg' => '消息服务短信、推送皆发送失败'];
                break;
            default:
                return ['code' => -1, 'msg' => '处理异常'];
                break;
        }
    }
}