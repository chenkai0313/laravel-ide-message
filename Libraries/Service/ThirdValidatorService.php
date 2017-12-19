<?php
/**
 * Created by PhpStorm.
 * User: pc16
 * Date: 2017/8/3
 * Time: 18:04
 */
namespace Libraries\Service;

class ThirdValidatorService
{
    /**
     * 第三方验证银行卡
     *
     * @param $back_num
     * @param $id_card
     * @param $mobile
     * @param $name
     * @return mixed
     */
    public function vaBankCard($back_num,$id_card,$mobile,$name)
    {
        $host = "http://yhsys.market.alicloudapi.com";
        $path = "/bank4";
        $method = "GET";
        $appcode = \Config::get('services.bank.appcode');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "bankCardNo=".$back_num."&identityNo=".$id_card."&mobileNo=".$mobile."&name=".$name;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $data=curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * 探知数据官网接口（暂时没买）验证银行卡
     * @param $back_num string  银行卡号
     * @param $id_card  string  身份证号
     * @param $mobile   string  手机号
     * @param $name     string  姓名
     * @return bool|mixed
     */
    public function vaBankCardTanZhi($back_num,$id_card,$mobile,$name)
    {
        $apiUrl='http://api.tanzhishuju.com/api/gateway';
        $params['method']   ='api.identity.bankcard4check';
        $params['apiKey']   =\Config::get('services.bank.apikey');
        $params['version']   ='1.0.0';
        $params["name"]        = $name;
        $params["identityNo"]  = $id_card;
        $params["bankCardNo"]  = $back_num;
        $params["mobileNo"]    = $mobile;
        $params["verifyChannel"]  = 'CUPS'; //获取 '全渠道'
        $paramsSign=$params;
        //按照key排序
        ksort($paramsSign);
        $sign=sha1(urldecode(http_build_query($paramsSign) .\Config::get('services.bank.apisecret')));//对该字符串进行 SHA-1 计算，得到签名，并转换成 16 进制小写编码
        $params['sign'] = $sign;
        $paramString = http_build_query($params);

        $content = $this->curl($apiUrl,$paramString,1,'tanzhishuju.com');
        return $content;
    }

    public function curl($url,$params=false,$ispost=0,$agent=''){
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , $agent );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , 1 );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        //var_dump($params);
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            myEcho("cURL Error: " . curl_error($ch));
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }
}