<?php

use Illuminate\Database\Seeder;

class MessageSmsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            ['1', 'SMS_86755055', '15906506710', '您的验证码为2645。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！', '{\"number\":\"2645\"}', '2', null, '2017-09-20 17:16:39'],
            ['2', 'SMS_86755055', '17682451396', '您的验证码为8537。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！', '{\"number\":\"8537\"}', '1', null, '2017-09-20 18:05:34'],
            ['3', 'SMS_86755055', '17682451396', '您的验证码为7491。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！', '{\"number\":\"7491\"}', '1', null, '2017-09-21 08:48:53'],
            ['4', 'SMS_86525063', '17682451396', '亲爱的17682451396，恭喜您成为畅想购会员。您只需身份认证后即可享受畅想购的服务！', '{\"name\":\"17682451396\"}', '1', null, '2017-09-21 08:49:22'],
            ['5', 'SMS_86755055', '15757390796', '您的验证码为6199。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！', '{\"number\":\"6199\"}', '1', null, '2017-09-21 09:05:46'],
            ['6', 'SMS_86755055', '17682451396', '您的验证码为5326。为了您的账户安全，请勿向他人泄露。感谢您的陪伴！', '{\"number\":\"5326\"}', '1', null, '2017-09-21 09:13:27'],
        ];

        $field = [
            'sms_id', 'send_template', 'phone', 'content', 'data', 'status', 'send_time', 'created_at'
        ];

        DB::table('sms')->insert(sql_batch_str($field, $params));
    }
}
