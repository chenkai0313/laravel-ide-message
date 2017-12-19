<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Exception;

class SendScheduleJPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jpush;

    // 推送开关
    protected $open_code = true;

    /**
     * 任务最大尝试次数
     * @var int
     */
    public $tries = 5;

    /**
     * 任务运行的超时时间。
     * @var int
     */
    public $timeout = 120;

    // 操作类型
    protected $operate_type = 1;

    // 发送成功状态
    protected $send_code = false;

    // 发送结果说明
    protected $send_msg = '';

    // 推送功能类型 message 通知\公告 push 推送
    protected $message_sign = 'push';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jupsh)
    {
        $this->jpush = $jupsh;

        if(isset($jupsh['operate_type'])){
            $this->operate_type = $jupsh['operate_type']; // 操作类型
        }

        if(isset($jupsh['message_sign'])){
            $this->message_sign = $jupsh['message_sign']; // 推送功能类型 message 通知\公告 push 推送
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 调用推送接口
        $push_return = false;
        $params = $this->jpush;

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
            common_log('jupsh','极光定时脚本推送失败',$params,(array)$push_return,'error');
        }

        $params['status'] = $this->send_code? '1':'2'; // 发送结果状态

        $this->push_update($params);
    }

    // 更新推送信息
    public function push_update($params){
        $data = [
            'status' => isset($params['status']) ? $params['status']:($this->send_code?1:2),
            'message_sign' => $this->message_sign,
        ];

        $data['id'] = isset($params['push_id'])?$params['push_id']:$params['id'];
        if($this->send_code){
            $data['msg_id'] = $params['push_return']['body']['msg_id'];
        }

        try{
            $return = \MessageService::message_update($data);
        }catch (\Exception $e){
            $return  = $e->getMessage();
            common_log('schedule','更新数据库失败',$data,(array)$return,'error');
            return $return;
        }

        return (array)$return;
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $e)
    {
        // 发送失败通知, etc...
        common_log('schedule','队列失败',[],(array)$e,'error');
    }
}
