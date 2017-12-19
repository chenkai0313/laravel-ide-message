<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Exception;

class SendMessageJPush implements ShouldQueue
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jupsh)
    {
        $this->jpush = $jupsh;
        $this->operate_type = $jupsh['operate_type'];
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
        if($params['status'] !== '0'){
            $params['send_time'] = date('Y-m-d H:i:s');
        }
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
        file_put_contents_log('jpush',$return,__FILE__,__LINE__);
    }
}
