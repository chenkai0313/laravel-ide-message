<?php
/**
 * Created by PhpStorm.
 * User: pc06
 * Date: 2017/8/23
 * Time: 19:35
 */

namespace Modules\Message\Models;

use Illuminate\Database\Eloquent\Model;

class MessagePush extends Model
{
    #表名
    protected $table = 'push';
    #主键
    protected $primaryKey = 'id';
    #支持字段批量赋值
    protected $fillable = ['msg_id', 'type', 'message_type', 'platform', 'send_type', 'user_id', 'title',
        'content', 'extra','is_direct','send_time', 'status'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        #只添加created_at不添加updated_at
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * 添加
     * @param $params
     * @return $this|Model
     */
    public static function MessagePushAdd($params)
    {
        $res = MessagePush::create($params);
        return $res->id;
    }

    /**
     * 推送列表
     * @param $params
     * @return $this|Model
     */
    public static function MessagePushList($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessagePush::Search($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'send_type', 'user_id', 'platform', 'title', 'content', 'extra', 'send_time', 'status', 'created_at')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }

    /**
     * 推送计数
     * @param $params
     */
    public static function MessagePushCount($params){
        $total = MessagePush::Search($params)->count();
        return $total;
    }

    #查询构造器
    public static function scopeSearch($query, $keyword){
        return $query->where(function($query) use($keyword) {
            // 消息类型
            if ( isset($keyword['message_type']) ) {
                $query->where('message_type', $keyword['message_type']);
            }
            // 获取信息发送类型
            if( isset($keyword['send_type']) ) {
                $query->where('send_type', 'like', '%'.strip_tags($keyword['send_type']).'%');
            }
            // 获取信息发送类型
            if( isset($keyword['title']) ) {
                $query->where('title', 'like', '%'.strip_tags($keyword['title']).'%');
            }
            // 直推？延迟？
            if( isset($keyword['is_direct']) ) {
                $query->where('is_direct', $keyword['is_direct']);
            }
            // 发送状态
            if( isset($keyword['status']) ) {
                $query->where('status', $keyword['status']);
            }
        });
    }

    /**
     * 更新
     * $params ['id'] 短消息记录id
     * $params ['is_read'] 读取状态
     * $params ['status'] 消息推送状态：0未处理，1处理成功，2处理失败
     */
    public static function pushEdit($params){
        return MessagePush::where(['id'=>$params['id']])
            ->update($params);
    }

    // 执行定时选择未发送与发送失败的记录
    public static function pushToSend($params){
        return MessagePush::select('id', 'msg_id', 'type', 'message_type', 'send_type', 'user_id', 'platform', 'title', 'content', 'extra', 'send_time', 'status', 'created_at')
            ->where('send_time','<',$params['send_time'])
            // ->where('is_direct', $params['is_direct'])
            ->where(function($query){
                $query->where('status','0')
                    ->orWhere(function($query){
                        $query->where('status', '2');
                    });
            })->orderBy('send_time', 'asc')
            ->get()->toArray();
    }

}