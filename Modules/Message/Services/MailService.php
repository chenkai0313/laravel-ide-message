<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/28
 * Time: 12:59
 */

namespace Modules\Message\Services;

use Mail;

use App\Events\Email;

class MailService
{
    // 接收方邮箱账号
    protected $email_to = '';

    /**
     * 发送纯文本邮件
     * $params ['text'] 邮件内容 string
     * $params ['email'] 接收方邮箱账号 string
     * $params ['title'] 邮件标题 string
     */
    public function sendText($params){
        $params['title'] = '维凯公司消息服务通知';
        if(isset($params['email'])){
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                $emailMsg = "非法邮箱格式";
                return ['code'=>10231,'msg'=>$emailMsg];
            }
        }else{
            $params['email'] = 'yefan@weknet.cn';
        }
        $params['text'] = isset($params['text'])?$params['text']:'邮件发送测试，现在时间为：'.date("Y-m-d H:i:s");

        //$this->email_to = $params['email'];
        try {
            // 处理业务、查找捕获可能存在的异常
            Mail::raw($params['text'], function ($message) use ($params){
                //$to = $this->email_to;
                $to = $params['email'];
                $message->to($to)->subject($params['title']);
            });
        } catch (\Exception $e) {
            // 抛出异常
            return $e->getMessage();
        }

        return ['code'=>1,'msg'=>'邮件发送成功'];
    }

    /**
     * 发送纯文本邮件-事件
     * $params ['text'] 邮件内容 string
     * $params ['email'] 接收方邮箱账号 string
     * $params ['title'] 邮件标题 strin
     */
    public function sendTextEvents($params){
        $params['title'] = '维凯公司消息服务通知';
        if(isset($params['email'])){
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                $emailMsg = "非法邮箱格式";
                return ['code'=>10231,'msg'=>$emailMsg];
            }
        }else{
            $params['email'] = 'yefan@weknet.cn';
        }
        $params['text'] = isset($params['text'])?$params['text']:'邮件发送测试，现在时间为：'.date("Y-m-d H:i:s");

        event(new Email($params)); // Event::fire(new PostSaved($post));
        return ['code'=>1,'msg'=>'邮件发送进入事件处理'];
    }

    /**
     * 发送图片类型附件邮件
     * $params ['text'] 邮件内容 string
     * $params ['email'] 接收方邮箱账号 string
     * $params ['title'] 邮件标题 string
     */
    public function sendAttachment($params){
        $name = '云师';
        $image = 'http://d.hiphotos.baidu.com/zhidao/pic/item/1ad5ad6eddc451da4ab93e2bb0fd5266d11632a6.jpg';
        try {
            // 处理业务、查找捕获可能存在的异常
            Mail::send('modules.email.email_template1',['name'=>$name,'imgPath'=>$image],function($message){
                $message ->to('yefan@weknet.cn')->subject('网络图片测试');
            });
        } catch (\Exception $e) {
            // 抛出异常
            return $e->getMessage();
        }

        return ['code'=>1,'msg'=>'邮件发送成功'];
    }
}