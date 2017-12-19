<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaseTableOfMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('notice') ) {
            Schema::create('notice', function(Blueprint $table)
            {
                $table->integer('id', true)->comment('会员短消息记录id');
                $table->string('msg_id', 30)->nullable()->comment('推送返回msg_id');
                $table->boolean('type')->default(1)->comment('消息分类（主）：1公告  2通知');
                $table->tinyInteger('message_type')->default(0)->comment('信息子类型：订单 激活白条 身份认证 还款提醒 催收提醒');
                $table->string('send_type', 30)->comment('发送类型:广播(all)、regis_id、tags、alias');
                $table->integer('user_id')->default(0)->comment('用户id');
                $table->string('title', 200)->comment('消息标题');
                $table->text('content', 65535)->comment('消息内容');
                $table->text('extra', 65535)->comment('消息扩展字段');
                $table->boolean('is_direct')->default(1)->comment('是否直接发送：0定时发送，1直接发送');
                $table->timestamp('send_time')->nullable()->comment('发送时间');
                $table->boolean('status')->default(0)->comment('消息推送状态：0未处理，1处理成功，2处理失败');
                $table->boolean('is_read')->default(0)->comment('是否已读：0未读，1已读');
                $table->timestamp('created_at')->nullable();
                $table->softDeletes();
                $table->tinyInteger('top')->default(0)->comment('置顶标志');
                $table->engine = 'MyISAM';
                $table->comment = '短消息记录表';
            });
        }

        if ( !Schema::hasTable('push') ) {
            Schema::create('push', function(Blueprint $table)
            {
                $table->integer('id', true)->comment('会员推送记录id');
                $table->string('msg_id', 30)->nullable()->comment('推送返回msg_id');
                $table->boolean('type')->default(1)->comment('消息分类（主）：1公告  2通知');
                $table->tinyInteger('message_type')->default(0)->comment('信息子类型：订单 激活白条 身份认证 还款提醒 催收提醒');
                $table->string('send_type', 30)->comment('发送类型:广播(all)、regis_id、tags、alias');
                $table->integer('user_id')->comment('用户id');
                $table->string('platform', 30)->comment('推送平台设置');
                $table->string('title', 200)->comment('消息标题');
                $table->text('content', 65535)->comment('消息内容');
                $table->text('extra', 65535)->comment('消息扩展字段');
                $table->boolean('is_direct')->default(1)->comment('是否直接发送：0定时发送，1直接发送');
                $table->timestamp('send_time')->nullable()->comment('发送时间');
                $table->boolean('status')->default(0)->comment('消息推送状态：0未处理，1处理成功，2处理失败');
                $table->timestamp('created_at')->nullable();
                $table->softDeletes();
                $table->engine = 'MyISAM';
                $table->comment = '推送消息记录表';
            });
        }

        if ( !Schema::hasTable('sms') ) {
            Schema::create('sms', function(Blueprint $table)
            {
                $table->integer('sms_id', true)->comment('短信ID');
                $table->boolean('send_role')->default(1)->comment('身份 1会员 2管理员');
                $table->integer('send_id')->comment('发送者ID、包含user_id\admin_id');
                $table->string('send_template', 50)->nullable()->comment('短信模板标识');
                $table->string('phone')->nullable()->comment('接收人手机号码');
                $table->string('content')->nullable()->comment('接收人内容');
                $table->string('data')->nullable()->comment('模板替换标签');
                $table->boolean('status')->default(0)->comment('发送状态（0未发送/1已发送/2发送失败');
                $table->timestamp('send_time')->nullable()->comment('发送时间');
                $table->timestamp('created_at')->nullable();
                $table->engine = 'MyISAM';
                $table->comment = '短信发送记录表';
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice');
        Schema::dropIfExists('push');
        Schema::dropIfExists('sms');
    }
}
