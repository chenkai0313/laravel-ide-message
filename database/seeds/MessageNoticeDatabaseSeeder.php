<?php

use Illuminate\Database\Seeder;

class MessageNoticeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            ['1', '54043196508348970', '1', '0', 'all', '0', '国庆放假通知', '国庆放假通知', '{\"type\":\"1\",\"message_type\":\"message_announcement\",\"data\":{\"code\":1,\"title\":\"\\u56fd\\u5e86\\u653e\\u5047\\u901a\\u77e5\",\"description\":\"\\u56fd\\u5e86\\u653e\\u5047\\u901a\\u77e5\"},\"title\":\"\\u56fd\\u5e86\\u653e\\u5047\\u901a\\u77e5\",\"description\":\"\\u56fd\\u5e86\\u653e\\u5047\\u901a\\u77e5\"}', '1', '2017-09-20 17:06:34', '1', '0', '2017-09-20 17:06:34', null, '0'],
            ['2', '65302195663115979', '1', '0', 'all', '0', '公告发布', '公告发布', '{\"type\":\"1\",\"message_type\":\"message_announcement\",\"data\":{\"code\":1,\"title\":\"\\u516c\\u544a\\u53d1\\u5e03\",\"description\":\"\\u516c\\u544a\\u53d1\\u5e03\"},\"title\":\"\\u516c\\u544a\\u53d1\\u5e03\",\"description\":\"\\u516c\\u544a\\u53d1\\u5e03\"}', '1', '2017-09-20 17:20:20', '1', '0', '2017-09-20 17:20:20', null, '0'],
            ['3', '54043196512415898', '2', '1', 'regis_id', '138', '会员审核-审核通过', '恭喜、审核通过', '{\"type\":\"2\",\"message_type\":\"user_apply\",\"data\":{\"code\":1,\"msg\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\",\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u901a\\u8fc7\",\"description\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\"},\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u901a\\u8fc7\",\"description\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\"}', '1', '2017-09-20 17:50:32', '1', '0', '2017-09-20 17:50:32', null, '0'],
            ['4', '65302195675604986', '2', '1', 'regis_id', '1', '会员审核-审核不通过', '不好意思,审核失败：测试拒绝', '{\"type\":\"2\",\"message_type\":\"user_apply\",\"data\":{\"code\":0,\"msg\":\"\\u4e0d\\u597d\\u610f\\u601d,\\u5ba1\\u6838\\u5931\\u8d25\\uff1a\\u6d4b\\u8bd5\\u62d2\\u7edd\",\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u4e0d\\u901a\\u8fc7\",\"description\":\"\\u4e0d\\u597d\\u610f\\u601d,\\u5ba1\\u6838\\u5931\\u8d25\\uff1a\\u6d4b\\u8bd5\\u62d2\\u7edd\"},\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u4e0d\\u901a\\u8fc7\",\"description\":\"\\u4e0d\\u597d\\u610f\\u601d,\\u5ba1\\u6838\\u5931\\u8d25\\uff1a\\u6d4b\\u8bd5\\u62d2\\u7edd\"}', '1', '2017-09-21 08:53:55', '1', '0', '2017-09-21 08:53:55', null, '0'],
            ['5', '65302195675664565', '2', '1', 'regis_id', '1', '会员审核-审核通过', '恭喜、审核通过', '{\"type\":\"2\",\"message_type\":\"user_apply\",\"data\":{\"code\":1,\"msg\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\",\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u901a\\u8fc7\",\"description\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\"},\"title\":\"\\u4f1a\\u5458\\u5ba1\\u6838-\\u5ba1\\u6838\\u901a\\u8fc7\",\"description\":\"\\u606d\\u559c\\u3001\\u5ba1\\u6838\\u901a\\u8fc7\"}', '1', '2017-09-21 08:57:58', '1', '0', '2017-09-21 08:57:58', null, '0'],
            ['6', '65302195675866808', '2', '1', 'regis_id', '1', '认证失败', '认证失败', '{\"type\":\"2\",\"message_type\":\"user_apply\",\"data\":{\"code\":1,\"title\":\"\\u8ba4\\u8bc1\\u5931\\u8d25\",\"description\":\"\\u8ba4\\u8bc1\\u5931\\u8d25\"},\"title\":\"\\u8ba4\\u8bc1\\u5931\\u8d25\",\"description\":\"\\u8ba4\\u8bc1\\u5931\\u8d25\"}', '1', '2017-09-21 09:10:44', '1', '0', '2017-09-21 09:10:44', null, '0'],
            ['7', '65302195675820844', '2', '2', 'regis_id', '1', '请激活白条', '请激活白条', '{\"type\":\"2\",\"message_type\":\"active_white\",\"data\":{\"code\":1,\"title\":\"\\u8bf7\\u6fc0\\u6d3b\\u767d\\u6761\",\"description\":\"\\u8bf7\\u6fc0\\u6d3b\\u767d\\u6761\"},\"title\":\"\\u8bf7\\u6fc0\\u6d3b\\u767d\\u6761\",\"description\":\"\\u8bf7\\u6fc0\\u6d3b\\u767d\\u6761\"}', '1', '2017-09-21 09:11:58', '1', '0', '2017-09-21 09:11:58', null, '0'],
        ];

        $field = [
            'id',
            'msg_id',
            'type',
            'message_type',
            'send_type',
            'user_id',
            'title',
            'content',
            'extra',
            'is_direct',
            'send_time',
            'status',
            'is_read',
            'created_at',
            'deleted_at',
            'top',
        ];

        DB::table('notice')->insert(sql_batch_str($field, $params));
    }
}
