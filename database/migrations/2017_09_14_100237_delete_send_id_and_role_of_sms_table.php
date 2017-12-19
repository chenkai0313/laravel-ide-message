<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteSendIdAndRoleOfSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms', function(Blueprint $table) {
            if (Schema::hasColumn('sms', 'send_role')) {
                $table->dropColumn('send_role');
            }
            if (Schema::hasColumn('sms', 'send_id')) {
                $table->dropColumn('send_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms', function(Blueprint $table) {
            if (!Schema::hasColumn('sms', 'send_role')) {
                $table->boolean('send_role')->default(1)->comment('身份 1会员 2管理员');
            }
            if (!Schema::hasColumn('sms', 'send_id')) {
                $table->integer('send_id')->comment('发送者ID、包含user_id\admin_id');
            }
        });
    }
}
