<?php
/**
 * 公共函数类
 */

/**
 * 生成订单唯一编号
 * @return  string
 */
function get_sn($prefix='')
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);
    return $prefix.date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}


/**
 * 获取jwt信息
 * $params array|string 参数名
 * @return  string
 */
function get_jwt($params=null)
{
    $payload = \JWTAuth::parseToken()->getPayload();
    return  $payload->get($params);
}
/**
 * 获取月初，下月初
 * @return mixed
 */
function get_month($time=null)
{
    if (is_null($time)) {
        $time=time();
    }
    $year=date('Y',$time);
    $month=date('m',$time);
    $return['this_month']=date("Y-m-d H:i:s",mktime(0,0,0,$month-1,1,$year));
    $return['next_month']=date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
    return $return;
}

/**
 * 获取传过来的时间的月初，下月初
 * @return mixed
 */
function get_month_time()
{
    $time=time();
    $year=date('Y',$time);
    $month=date('m',$time);
    $return['this_month']=date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
    $return['next_month']=date("Y-m-d H:i:s",mktime(0,0,0,$month+1,1,$year));
    return $return;
}
/**
 * 获取jwt信息中的user_id
 * @return  string
 */
function get_user_id()
{
    $payload = \JWTAuth::parseToken()->getPayload();
    return  $payload->get('user_id');
}

/**
 * 获取jwt信息中的admin_id
 * @return  string
 */
function get_admin_id()
{
    $payload = \JWTAuth::parseToken()->getPayload();
    return  $payload->get('admin_id');
}

/**
 * get请求
 * @return  array
 */
function vget($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	curl_close($ch);
	//-------请求为空
	if(empty($response)){
		return null;
	}
	return $response;
}

/**
 * post请求
 * @return  array
 */
function vpost($url,$data){
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * 随机生成一个字符串
 * @param $length 长度
 * @return string
 */
function getRandomkeys($length = 8)
{
    $key = "";
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXY';    //字符池
    for($i=0; $i<$length; $i++)
    {
        $key .= $pattern{mt_rand(0,60)};    //生成php随机数
    }
    return $key;
}
/**
 * 获取常量的缓存
 * @param $key
 * @param $type
 * $key包含['statement_date','repayment_date']
 * $type包含['credit']
 * @return mixed
 */
function get_constant_cache($key,$type)
{
    return BackendConstantService::getConstantCache($key,$type);
}

/**
 * 批量插入SQL拼接
 * @param $field
 * @param $params
 * @return array
 */
function sql_batch_str($field, $params) {
    $data = [];
    foreach ($params as $key => $value) {
        $data_info = [];
        foreach ($value as $k1 => $v1) {
            foreach ($field as $k2 => $v2) {
                if ($k1 == $k2) {
                    $data_info[$v2] = $v1;
                    continue;
                }
            }
        }
        $data[] = $data_info;
    }
    return $data;
}

/**
 * 默认记录日志公共方法
 * @param $file_name string 文件名
 * @param $message string 消息内容
 * @param $data array 访问参数
 * @param $returns array 返回结果
 * @param $level string 日志等级
 */
function common_log($file_name,$message,$data,$returns,$level='info'){
    // 拼接文件名
    $file_name = $file_name.'-'.date('Y-m-d').'-'.$level;
    // 访问生成日志文件
    \Log::useFiles(storage_path().'/logs/'.$file_name.'.log',$level);
    // 传参数据写入
    if(!empty($data)){
        \Log::log($level,'传入参数',$data);
    }
    // 返回日志写入
    \Log::log($level,$message,$returns);
    return true;
}

/**
 * 打印日志定位问题
 * @param $file_pre string 文件名
 * @param $params array 数据参数
 * @param $files string 打日志文件路径 __FILE__
 * @param $line string 定位所打日志位置 __LINE__
 * example file_put_contents_log('message',$params,__FILE__,__LINE__);
 */
function file_put_contents_log($file_pre='log',$params,$files='',$line=''){
    $location = '';
    if($files != ''){
        $location .= '[file:'.$files.']';
    }
    if($line != ''){
        $location .= '[line: '.$line.']';
    }
    file_put_contents(
        storage_path().'/logs/'.$file_pre.'-'.date('Y-m-d').'-logs.log',
        $location.'[data]'.var_export($params, true).PHP_EOL,
        FILE_APPEND
    );
    return true;
}

/**
 * 消息服务接收参数整理
 * @param $services string 1 push  2 sms
 * @param $params array 数据参数
 * return array
 * example message_params_filter('1',$params);
 */
function message_params_filter($services="1",$params){
    $return = [];
    if($services == "1" || $services == "push"){
        $return = array_merge($return,$params['send_params']);
        foreach ($return as $key=>$value) {
            if($key == "data" || $key == "send_template"){
                unset($return[$key]);
            }else{
                continue;
            }
        }
        $return['object_info'] = $params['object_info'];
        foreach ($return['object_info'] as $key=>$value) {
            unset($return['object_info'][$key]['user_mobile']);
        }
        if(isset($params['result'])){
            $return['result'] = $params['result'];
        }
    }else if($services == "2" || $services == "sms"){
        $return['content'] = $params['send_params']['content'];
        $return['data'] = $params['send_params']['data'];
        $return['send_template'] = $params['send_params']['send_template'];

        $return['mobile'] = [];
        foreach ($params['object_info'] as $key=>$value) {
            $return['user_mobile'][] = $params['object_info'][$key]['user_mobile'];
        }
    }else{
        $return = $params;
    }

    return $return;
}