<?php
/**
 * Created by PhpStorm.
 * User: 张燕
 * Date: 2017/8/14
 * Time: 9:56
 */
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiTest extends TestCase
{

public $data = ['Authorization'=>'Bearer ' . 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcm9tIjoidXNlciIsInVzZXJfaWQiOjE4LCJ1c2VyX21vYmlsZSI6IjE3NjgyNDUxMzk2Iiwic3ViIjoxOCwiaXNzIjoiaHR0cDovL2lkZWFidXkueGluLmNuL2FwaS91c2VyLWxvZ2luIiwiaWF0IjoxNTAyODY5NDQ4LCJleHAiOjE1MDQwNzkwNDgsIm5iZiI6MTUwMjg2OTQ0OCwianRpIjoiMElGYnMzVGtwejMyTlNubCJ9.Rkly1f0XUsXS5uZ3ng2SVmKDCWhdiksy7DzIZlHCQ04'];
public $admin_token = ['Authorization'=>'Bearer ' . 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcm9tIjoiYWRtaW4iLCJhZG1pbl9pZCI6MSwiYWRtaW5fbmljayI6Ilx1NjYzNVx1NzlmMCIsInN1YiI6MSwiaXNzIjoiaHR0cDovL2lkZWFidXkueGluLmNuL2JhY2tlbmQvYWRtaW4tbG9naW4iLCJpYXQiOjE1MDI4NDYxNjAsImV4cCI6MTUwNDA1NTc2MCwibmJmIjoxNTAyODQ2MTYwLCJqdGkiOiJDWkJsZm5IQ085VkVobFBXIn0.W8FD4o-B8qdqKXaNPjQ_yWSKWHO4MRODXk-S8VzJZvE'];

    public function testExample()
    {
        $this->assertTrue(true);
    }




    /*
     *  测试用户登录
     *  所需参数
     *  $params 'user_mobile','user_password'
    */

    public function testuser_login()
    {
        // $this->withoutMiddleware('jwtUser');
        $response = $this->call('POST', 'http://ideabuy.xin.cn/api/user-login',['user_mobile'=>'17682451396','user_password'=>'z123123123'])
            ->assertJsonFragment([
                'msg'=>'登录成功',
            ]);



    }




    /*
    *   测试新增联系人
    *   所需参数
    *   $params 'link_mobile','link_man','link_relation'
    *   缺少参数为空的判断
    *
   */

    public function testuserlink_add()
    {
        $response = $this->POST('http://ideabuy.xin.cn/api/userlink-add',['link_mobile'=>'15221781232','link_man'=>'lala','link_relation'=>'firend'],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'添加常用联系人成功',
            ]);

    }


    /*
      *   测试完善信息
      *   所需参数
      *   $params 'user_portrait'
      *
     */

    public function testuserinfo_add()
    {
        $response = $this->POST('http://ideabuy.xin.cn/api/userinfo-add',['user_portrait'=>'111111'],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'完善信息成功',
            ]);

    }


    /*
*   用户详情接口(完善信息用)
*/
    public function testuserinfo_detail()
    {
        $response = $this->get('http://ideabuy.xin.cn/api/userinfo-detail',$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'查询成功',
            ]);

    }


//    /*
//*   我的白条，测试获取banner图
//*/
//    public function testwhite_getbanner()
//    {
//        $response = $this->get('http://ideabuy.xin.cn/api/white-getbanner')
////        $this->assertEquals('ture',$response->getcontent());
//            ->assertJsonStructure([
//                'data'=>[],
//            ]);
//
//    }

//    /*
//*   激活我的白条
//*/
//    public function testuser_active()
//    {
//        $response = $this->get('http://ideabuy.xin.cn/api/user-active',$this->data)
////        $this->assertEquals('ture',$response->getcontent());
//            ->assertJsonFragment([
//                'msg'=>'激活白条成功',
//            ]);
//
//    }

    /*
*   授信二维码JSON
*/
    public function testuser_creditcode()
    {
        $response = $this->get('http://ideabuy.xin.cn/api/user-creditcode?credit_code=123',$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'查询成功',
            ]);

    }

    /*
*   我的首页
*/
    public function testuser_getinstalltypeplan()
    {
        $response = $this->post('http://ideabuy.xin.cn/api/user-getinstalltypeplan',['amount'=>12000],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonStructure([
                'data'=>[],
            ]);

    }





    /*
    *   测试用户身份证添加
    *   所需参数
    *   $params 'user_idcard','real_name'
    *
    */

    public function testuser_editidcard()
    {
        $response = $this->POST('http://ideabuy.xin.cn/api/user-editidcard',['user_idcard'=>'330225199511252281','real_name'=>'张燕'],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'身份证添加成功',
            ]);

    }

    /*
   *   测试用户身份证照片添加
   *   所需参数
   *   $params 'file'
   *
   */

    public function testuser_editidimg()
    {
        $response = $this->POST('http://ideabuy.xin.cn/api/user-editidimg',['file'=>'test',],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'身份证照片添加成功',
            ]);

    }


    /*
    *   测试用户设置交易密码
    *   所需参数
    *   $params 'pay_password'
    *
    */
    public function testuser_setpaypwd()
    {
        $response = $this->POST('http://ideabuy.xin.cn/api/user-setpaypwd',['pay_password'=>'11111111111'],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'设置交易密码成功',
            ]);

    }







    /*
    *   测试获订单列表
    *   所需参数
    *   $params 'limit'，'page'
    *
    */
    public function testorder_list()
    {

        $response = $this->get('http://ideabuy.xin.cn/api/order-list?limit=10&page=1',$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonStructure([
                'data'=>[],
            ]);

    }

    /*
*   测试用户头像上传更新信息
*   所需参数
*   $params 'limit'，'page'
*
*/
    public function testimg_upload()
    {

        $response = $this->post('http://ideabuy.xin.cn/api/userheadimg-upload',['user_portrait'=>'dasdasda'],$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'头像修改成功',
            ]);

    }

    /*
*   测试获取真实姓名
*   所需参数
*   $params 'pay_password'，'confirm_pay_pwd'，'code'
*
*/
    public function testuser_card()
    {

        $response = $this->get('http://ideabuy.xin.cn/api/user-card',$this->data)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonStructure([
                'data'=>[],
            ]);

    }



    /*
    *   测试查询管理员列表
    *   所需参数
    *   $params 'admin_name','admin_password'
    *
    */

    public function testadmin_list()
    {
        $response = $this->get('http://ideabuy.xin.cn/backend/admin-list?limit=9&page=1',$this->admin_token)
//        $this->assertEquals('ture',$response->getcontent());
            ->assertJsonStructure([
                'data'=>[],
            ]);

    }








    /**
     *
     *   测试短信发送(3:重置交易密码，4：绑定银行卡，6：登陆后重置密码)
     *   所需参数
     *   $params 'moblie','type'
     *
     */
//    public function testuser_sms()
//    {
//        $response = $this->POST('http://ideabuy.xin.cn/api/user-sendsms',['type'=>'4'],$this->data)
////        $this->assertEquals('ture',$response->getcontent());
//            ->assertJsonFragment([
//                'msg'=>'短信发送成功',
//            ]);
//
//    }



//    /**
//     *
//     *   测试永久缓存接口
//     *   所需参数
//     *   $params 'key','val'
//     *
//     */
//    public function testcache_add()
//    {
//        $response = $this->get('http://ideabuy.xin.cn/api/cache-add?key=2&val=1')
////        $this->assertEquals('ture',$response->getcontent());
//            ->assertJsonFragment([
//                'msg'=>'缓存更新成功!',
//            ]);
//
//    }




    /**
     *
     *   测试查询地址详情
     *   所需参数
     *   $params 'address_id'
     *
     */
    public function testaddress_detail()
    {
        $response = $this->get('http://ideabuy.xin.cn/api/user-addressdetail?address_id=28', $this->data)
            // $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'查询成功',
            ]);

    }



    /**
     *
     *   测试修改地址
     *   所需参数
     *   $params 'address_id','province','city','district','street','address'
     *
     */
    public function testaddress_edit()
    {
        $response = $this->post('http://ideabuy.xin.cn/api/user-addressedit',['address_id'=>30,'province'=>11,'city'=>1101,'district'=>110101,'street'=>110101,'address'=>110101006], $this->data)
            // $this->assertEquals('ture',$response->getcontent());
            ->assertJsonFragment([
                'msg'=>'更新成功',
            ]);

    }


    /**
     *
     *   测试后台查询订单详情
     *   所需参数
     *   $params 'order_id'
     *
     */
    public function testorder_detail()
    {
        $response = $this->get('http://ideabuy.xin.cn/backend/order-detail?order_id=282', $this->admin_token)
            // $this->assertEquals('ture',$response->getcontent());
            ->assertJsonStructure([
                'data'=>[],
            ]);

    }


    //测试删除地址
//    public function testaddressdelete()
//    {
//        $response = $this->POST('api/user-addressdelete',['address_id'=>'6'],
//
//            ['Authorization' => 'Bearer ' . 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcm9tIjoidXNlciIsInVzZXJfaWQiOjEwLCJ1c2VyX21vYmlsZSI6IjE1MzgxOTEyODk1Iiwic3ViIjoxMCwiaXNzIjoiaHR0cDovL2lkZWFidXkueGluLmNuL2FwaS91c2VyLWxvZ2luIiwiaWF0IjoxNTAyMjQ0NTAwLCJleHAiOjE1MDM0NTQxMDAsIm5iZiI6MTUwMjI0NDUwMCwianRpIjoialBXN1VMaU1GY0ZVNDFvQyJ9.UJH1b_HlziUVn3hpCzV1j_sF4MTS4ASRMTUQMZasCd4']
//        );
//        $this->assertEquals('ture',$response->getcontent());
////            ->assertJsonFragment([
////                'msg'=>'删除成功',
////            ]);
//    }


}
