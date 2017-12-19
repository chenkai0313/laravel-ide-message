<?php

namespace App\Listeners;

use Mail;
use App\Events\Email;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailEventListener
{
    // 传入参数
    protected $email = [];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  Email  $event
     * @return void
     */
    public function handle(Email $event)
    {
        // 初始化参数
        $this->email = $event->email;
        $params = $this->email;

        try {
            // 处理业务、查找捕获可能存在的异常
            Mail::raw($params['text'], function ($message) use ($params){
                //$to = $this->email_to;
                $to = $params['email'];
                $message->to($to)->subject($params['title']);
            });
        } catch (\Exception $e) {
            // 抛出异常
            $return = $e->getMessage();
            common_log('email','发送邮件异常',$params,(array)$return);
        }
    }
}
