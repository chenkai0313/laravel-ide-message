<?php

namespace Modules\Message\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageNotice extends Model
{
    use SoftDeletes;

    //表名
    protected $table = 'notice';
    //主键
    protected $primaryKey = 'id';
    //支持字段批量赋值
    protected $fillable = ['msg_id', 'type', 'message_type', 'send_type', 'user_id', 'title',
        'content', 'extra','is_direct', 'send_time', 'status', 'is_read'];

    protected $dates = ['deleted_at'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        #只添加created_at不添加updated_at\deleted_at
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * 添加
     * @param $params
     * @return $this|Model
     */
    public static function MessageNoticeAdd($params)
    {
        $res = MessageNotice::create($params);
        return $res->id;
    }

    public static function MessageList($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::SearchList($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }

    /**
     * 短消息 列表-通知
     * @param int $limit 每页显示数量
     * @param int $page 当前页数
     * @param string $keyword 关键词
     * @return array
     */
    public static function  MessageNoticeList($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::Search($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }
    /**
     * 短消息 列表-通知-后台
     * @param int $limit 每页显示数量
     * @param int $page 当前页数
     * @param string $keyword 关键词
     * @return array
     */
    public static function  MessageNoticeListBackend($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::SearchList($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read', 'status')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }

    /**
     * 短消息 列表-公告
     * @param int $limit 每页显示数量
     * @param int $page 当前页数
     * @param string $keyword 关键词
     * @return array
     */
    public static function  MessageAnnounceList($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::Search($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read')
            ->orderBy('top', 'desc')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }

    /**
     * 短消息 列表-公告-后台
     * @param int $limit 每页显示数量
     * @param int $page 当前页数
     * @param string $keyword 关键词
     * @return array
     */
    public static function  MessageAnnounceListBackend($params){
        $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::SearchList($params)
            ->select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read', 'status', 'top')
            ->orderBy('top', 'desc')
            ->orderBy('send_time', 'desc')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $list;
    }

    // 统计、计数
    public static function MessageCount($params){
        $total = MessageNotice::Search($params)->count();
        return $total;
    }

    // 后台计数
    public static function MessageListCount($params){
        $total = MessageNotice::SearchList($params)->count();
        return $total;
    }

    /**
     * 统计用户未读消息
     * @params user_id 用户id
     */
    public static function messageCountUnRead($params){
        $total = MessageNotice::where('user_id','=', $params['user_id'])
            ->where('is_read','=', '0')
            ->where('type','=', '2')
            ->count();
        return $total;
    }

    #查询构造器
    #status 消息推送状态：0未处理，1处理成功，2处理失败
    public static function scopeSearch($query, $keyword){
        return $query->where(function($query) use($keyword) {
            // 获取数据-公告 只获取一个月内
            if ($keyword['type'] == 1 ) {
                $query->where('send_time', '>', date("Y-m-d", strtotime("-1 months"))." 00:00:00")->where('type','1');
            }
            // 获取数据-通知
            if($keyword['type'] == 2) {
                if(isset($keyword['user_id'])){
                    $query->where('user_id', strip_tags($keyword['user_id']))->where('type','2');
                }
            }
        })->where('status', '1');
    }

    /**
     * 列表查询构造 - 后台
     */
    public static function scopeSearchList($query, $keyword){
        return $query->where(function($query) use($keyword) {
            // 获取数据-通知
            if(isset($keyword['type'])){
                $query->where('type',$keyword['type']);
            }
            // 消息类型
            if ( isset($keyword['message_type']) ) {
                $query->where('message_type', $keyword['message_type']);
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

    // 执行定时选择未发送与发送失败的记录
    public static function messageToSend($params){
        return MessageNotice::select('id', 'msg_id', 'type', 'message_type', 'send_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read')
            ->where('send_time','<',$params['send_time'])
            ->where(function($query){
                $query->where('status','0')
                    ->orWhere(function($query){
                        $query->where('status', '2');
                    });
            })->orderBy('send_time','asc')
            ->get()->toArray();
    }

    /**
     * 更新
     * $params ['id'] 短消息记录id
     * $params ['is_read'] 读取状态
     * $params ['status'] 消息推送状态：0未处理，1处理成功，2处理失败
     */
    public static function messageEdit($params){
        return MessageNotice::where(['id'=>$params['id']])
            ->update($params);
//            ->update(['is_read' => $params['is_read']]);
    }

    // 批量设置已读状态
    public static function messageSetRead($params){
        return MessageNotice::whereIn('id',$params['id'])
            ->update(['is_read' => $params['is_read']]);
    }

    /**
     * 删除消息
     * $params ['id'] 短消息记录id
     */
    public static function messageDelete($params){
        $result = MessageNotice::where('id', $params['id'])->delete();
        return $result;
    }

    /**
     * 获取最高置顶顺序数据
     */
    public static function messageTopData(){
        $result = MessageNotice::select('id', 'msg_id', 'type', 'message_type', 'user_id', 'title', 'content', 'extra', 'send_time', 'is_read', 'top')
            ->where('type','=','1')
            ->where('status','=','1')
            ->orderBy('top', 'desc')
            ->orderBy('send_time', 'desc')
            ->first();
        return $result;
    }

    /**
     * 清空置顶
     */
    public static function messageTopClean(){
        $result = MessageNotice::where('type',1)
            ->update(['top' =>0]);
        return $result;
    }

    /**
     * 短消息 列表-公告-pc
     * @param int $limit 每页显示数量
     * @param int $page 当前页数
     * @param string $keyword 关键词
     * @return array
     */
    public static function  MessageAnnounceListPC($params){
        // $offset=($params['page']-1)*$params['limit'];
        $list = MessageNotice::select('id', 'msg_id', 'type', 'message_type', 'title', 'content', 'send_time', 'status', 'top')
            ->orderBy('top', 'desc')
            ->orderBy('send_time', 'desc')
            ->skip(0)->take($params['limit'])->get()->toArray();
        return $list;
    }

}