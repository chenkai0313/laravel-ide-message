<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Exception;
use Overtrue\EasySms\EasySms;

class SendMessageSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sms_data;

    // 短信发送开关
    protected $open_code = true;

    // 单条数据、多条数据（ false\true ）默认FALSE
    protected $array_status = false;

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
     * Create a new job instance.
     * 初始化
     * @return void
     */
    public function __construct($sms_data)
    {
        // 判断多条短信
        if( isset($sms_data['array_status'])){
            if($sms_data['array_status']){
                $this->array_status = $sms_data['array_status'];
            }
            unset($sms_data['array_status']);
        }
        // 发送数据
        $this->sms_data = $sms_data;
        // 短信开关
        $this->open_code = \Config::get('services.sms.sms_send_entry');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 发送配置参数初始化
        $Config = \Config::get('sms');

        $params = $this->sms_data;
        if($this->array_status){
            foreach ($params as $value){
                $this->send($Config,$value);
            }
        }else{
            $this->send($Config,$params);
        }
    }

    // 发送
    private function send($Config,$params){
        $easySmsSend = new EasySms($Config);
        try {
            if ($this->open_code) {
                $easySmsSend->send($params['mobile'], [
                    'content' => $params['content'],
                    'template' => $params['send_template'],
                    'data' => $params['data'],
                ]);
                $params['status'] = '1';
            }else{
                $params['status'] = '0';
            }
        } catch (\Exception $e) {
            $params['status'] = '2';
            \Log::info('短信发送失败：',$e);
        }

        $this->sms_record($params);
    }

    // 存储发送记录
    private function sms_record($params){
        if($params['status'] !== '0'){
            $params['send_time'] = date('Y-m-d H:i:s');
        }
        return \SmsService::send_record($params);
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
        common_log("sms-send-failed","短信发送失败",[],(array)$e,$level='error');
    }
}
