<?php
/**
 * Created by PhpStorm.
 * User: pc06
 * Date: 2017/8/30
 * Time: 15:27
 */

namespace Modules\Message\Models;

use Illuminate\Database\Eloquent\Model;

class MessageSms extends Model
{
    protected $table      = 'sms';
    protected $primaryKey = 'sms_id';
    protected $fillable = array('send_template','phone','content','data','status','send_time');

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public static function smsList($params){
        $offset=($params['page']-1)*$params['limit'];
        $result = MessageSms::Search($params)
            ->select('sms_id','send_template','phone','content','status','send_time')
            ->skip($offset)->take($params['limit'])->get()->toArray();
        return $result;
    }

    public static function smsCount($params){
        $total = MessageSms::Search($params)->count();
        // $total = MessageSms::count();
        return $total;
    }

    public static function scopeSearch($query, $keyword){
        return $query->where(function($query) use($keyword) {
            // 短信内容
            if( isset($keyword['content']) ){
                $query->orWhere('content', 'like', '%'.strip_tags($keyword['content']).'%');
            }
            // 手机号码
            if( isset($keyword['phone']) ){
                $query->orWhere('phone', $keyword['phone']);
            }
        });
    }

    /**
     * 添加
     */
    public static function smsRecordAdd($params)
    {
        $res = MessageSms::create($params);
        return $res->id;
    }
}