<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Exception;

class SendMessagePush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 服务商选择
     * jupsh : 极光
     * xm : 小米
     */
    protected $service_provider = "jpush";

    /**
     * 推送参数
     */
    protected $push_params;

    /**
     * 推送开关
     */
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

    /**
     * 操作类型
     */
    protected $operate_type = 1;

    /**
     * 发送成功状态
     */
    protected $send_code = false;

    /**
     * 发送结果说明
     */
    protected $send_msg = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->push_params = $params;
        $this->operate_type = $params['operate_type'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = $this->push_params;
        $device_token = $params['device_token'];
        //if(isset($params['registration_id']))
        /*if(isset($params['device_services'])){

        }*/
        if(isset($device_token['jpush_token'])){
            $this->jpush($params);
        }
        /*foreach($device_token as $key=>$value){
            $services = explode("_",$key);
            $this->$services['0']($params);
        }*/
    }

    /**
     * 极光推送入口
     */
    public function jpush($params){
        $push_return = false;
        $params['services'] = "jpush";
        $data = $params;
        $data['registration_id'] = $params['device_token']['jpush_token'];
        $data['alias'] = $params['user_id'];
        $data['description'] = $params['content'];
        $data['audience'] = $params['send_object'];
        if(isset($params['group_tags'])){
            $data['tags'] = $params['group_tags'];
        }

        if($this->operate_type == 1){
            $push_return = \jpush::pushSend($data);
        }
        if($this->operate_type == 2){
            $push_return = \jpush::messageSend($data);
        }
        if($this->operate_type == 3){
            $push_return = \jpush::jpushSend($data);
        }
        // 处理推送结果
        if(isset($push_return['http_code']) && $push_return['http_code'] == 200){
            $this->send_code = true;
            $this->return_msg = $push_return;
            $this->send_msg = '极光推送成功';
        }else{
            $this->send_msg = "极光服务器推送失败";
        }

        $params['status'] = $this->send_code? '1':'2'; // 发送结果状态
        $params['msg_id'] = $this->send_code?$push_return['body']['msg_id']:'';

        $this->push_record($params);
    }

    // 存储发送记录
    private function push_record($params){
        \MessageService::table_push($params);
        \MessageService::table_notice($params);
        return true;
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
        $return = $e->getMessage();

        file_put_contents_log('push',$return,__FILE__,__LINE__);
    }
}
