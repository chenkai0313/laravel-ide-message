<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17 0017
 * Time: 上午 9:22
 */

namespace Libraries\Service;

use JPush\Client as jpush;

class JpushService
{
    public $Jpush;

    protected $platform = ["android", "ios"];

    protected $audience = 'regis_id'; // 推送目标 广播(all)、regis_id、tags、alias

    protected $apns_production = false; // true表示发送到生产环境(默认值)，false为开发环境 ios

    protected $log_null = true; // 记录日志?true:false

    protected $log_path = '/logs/message_services_jpush.log'; // 日志文件路径

    public function __construct()
    {
        // 是否打印日志
        $this->log_null = \Config::get('services.jpush.log_null');
        $this->log_path = !$this->log_null?null:storage_path().$this->log_path;

        // 初始化推送参数
        $this->Jpush = new jpush(\Config::get('services.jpush.key'), \Config::get('services.jpush.secret'),$this->log_path);
        $this->apns_production = \Config::get('services.jpush.apns_production');
    }

    /**
     * 推送and短消息 alias\registration_id
     * @param int $audience 推送目标
     * @param string $platform 发送平台
     * @param string $alias 别名、统一用user_id
     * @param string $registration_id 唯一设备标识
     * @param string $tags 标签
     * @param string $title 推送标题
     * @param string $description 推送描述
     * @param array $extra 额外自定义参数 array
     */
    public function jpushSend($params)
    {
        if (isset($params['platform'])) {
            $this->platform = $params['platform'];
        }
        if (isset($params['audience'])) {
            $this->audience = $params['audience'];
        }
        $notification = [
            'title' => $params['title'],
            'description' => $params['description'],
        ];
        if (isset($params['extra']) && $params['extra']) {
            $notification['extras'] = $params['extra'];
        }
        $ios = $android = $notification;
        $pusher = $this->Jpush->push();
        $pusher->setPlatform($this->platform);
        if ($this->audience == 'all') {
            $pusher->setAudience('all');
            $ios['badge'] = 0;
        } else if ($this->audience == 'regis_id' || $this->audience == 'alias') {
            $pusher->addAlias((string)$params['alias']);
            $pusher->addRegistrationId($params['registration_id']);
        } else if ($this->audience == 'tags') {
            $pusher->addTag($params['tags']);
        }
        $pusher->setNotificationAlert($params['alert']);
        $pusher->androidNotification($params['alert'], $android);
        $pusher->iosNotification($params['alert'], $ios);
        $pusher->message($params['title']."：".$params['description'], $notification);
        $pusher->options(array(
            "apns_production" => $this->apns_production
        ));

        try {
            $return = $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            return $e->getMessage();
        }
        return $return;
    }

    /**
     * 短消息 自定义消息
     * @param int $audience 推送目标 1广播(all)、2regis_id、3tags、4alias
     * @param string $platform 发送平台
     * @param string $alias 别名、统一用user_id
     * @param string $registration_id 唯一设备标识
     * @param string $tags 标签
     * @param string $title 推送标题
     * @param string $description 推送描述
     * @param array $extra 额外自定义参数 array
     */
    public function messageSend($params)
    {
        if (isset($params['platform'])) {
            $this->platform = $params['platform'];
        }
        if (isset($params['audience'])) {
            $this->audience = $params['audience'];
        }
        $notification = [
            'title' => $params['title'],
            'description' => $params['description'],
        ];
        if (isset($params['extra']) && $params['extra']) {
            $notification['extras'] = $params['extra'];
        }
        $pusher = $this->Jpush->push();
        $pusher->setPlatform($this->platform);
        if ($this->audience == 'all') {
            $pusher->setAudience('all');
        } else if ($this->audience == 'regis_id' || $this->audience == 'alias') {
            $pusher->addAlias((string)$params['alias']);
            $pusher->addRegistrationId($params['registration_id']);
        } else if ($this->audience == 'tags') {
            $pusher->addTag($params['tags']);
        }
        $pusher->message($params['title']."：".$params['description'], $notification);
        $pusher->options(array(
            "apns_production" => $this->apns_production  // true表示发送到生产环境(默认值)，false为开发环境
        ));

        try {
            $return = $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            return $e->getMessage();
        }
        return $return;
    }

    /**
     * 推送 android ios
     * @param int $audience 推送目标 1广播(all)、2regis_id、3tags、4alias
     * @param string $platform 发送平台
     * @param string $alias 别名、统一用user_id
     * @param string $registration_id 唯一设备标识
     * @param string $tags 标签
     * @param string $title 推送标题
     * @param string $description 推送描述
     * @param array $extra 额外自定义参数 array
     */
    public function pushSend($params)
    {
        if (isset($params['platform'])) {
            $this->platform = $params['platform'];
        }
        if (isset($params['audience'])) {
            $this->audience = $params['audience'];
        }
        $notification = [
            'title' => $params['title'],
            'description' => $params['description'],
        ];
        if (isset($params['extra']) && $params['extra']) {
            $notification['extras'] = $params['extra'];
        }
        $ios = $android = $notification;
        $pusher = $this->Jpush->push();
        $pusher->setPlatform($this->platform);
        if ($this->audience == 'all') {
            $pusher->setAudience('all');
            $ios['badge'] = 0;
        } else if ($this->audience == 'regis_id' || $this->audience == 'alias') {
            $pusher->addAlias((string)$params['alias']);
            $pusher->addRegistrationId($params['registration_id']);
        } else if ($this->audience == 'tags') {
            $pusher->addTag($params['tags']);
        }
        $pusher->setNotificationAlert($params['alert']);
        $pusher->androidNotification($params['alert'], $android);
        $pusher->iosNotification($params['alert'], $ios);
        $pusher->options(array(
            "apns_production" => $this->apns_production  // true表示发送到生产环境(默认值)，false为开发环境
        ));

        try {
            $return = $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            return $e->getMessage();
        }
        return $return;
    }

    // 设置用户极光服务器的alias 用user_id替代
    public function userAliasSend($alias)
    {
        $pusher = $this->Jpush->device();
        try {
            $return = $pusher->updateAlias($alias['registration_id'], (string)$alias['user_id']);
        } catch (\JPush\Exceptions\JPushException $e) {
            return $e->getMessage();
        }
        return $return;
    }

}