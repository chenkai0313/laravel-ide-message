<?php

namespace Libraries\Service;

use OSS\Core\OssException;
use OSS\OssClient;

class OssService
{
    public $ossClient;
    private $bucket;
    public $filePath;
    protected $host;
    public function __construct()
    {
        $this->ossClient = new OssClient(\Config::get('services.oss.key'),\Config::get('services.oss.secret'),\Config::get('services.oss.endpoint'));
        $this->filePath = \Config::get('services.oss.filepath');
        $this->bucket = \Config::get('services.oss.bucket');

        //$this->host = \Config::get('services.oss.host');

        $this->host = \Config::get('services.oss.host');

    }
    /**
     * 本地图片转base64
     * @param $file  图片信息
     * @param $type  类型(1.用户头像;2.产品分类图)
     * @return array
     */
    public function encodeBase64Image($file) {
        $filename = $file['tmp_name'];
        $filetype = $file['type'];
        if ($filename) {
            $imgbinary = fread(fopen($filename, "r"), filesize($filename));
            return 'data:' . $filetype . ';base64,' . base64_encode($imgbinary);
        }
    }
    /**
     * 单图上传 已整合 base64格式
     * @param $file  base64图片数据
     * @param $type  类型(1.用户头像;2.产品分类图;3.产品相关图)
     * @return array
     */
    public function uploadBase64Image($file,$type)
    {
        switch($type){
            case 1: $dir = "article"; break; //文章图
            case 2: $dir = "ad"; break;//广告图
            default : $dir = '';
        }
        if($dir){
            $dir = $dir.'/'.date('Y',time()).'/'.date('m',time()).'/'.date('d',time());
            $file_name = md5(time().mt_rand(1000,9999)).'.jpg';
            $file_path = $dir.'/'.$file_name;
            #创建目录
            if(!is_dir($this->filePath)){
                //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
                $res = mkdir(iconv("UTF-8", "GBK", $this->filePath),0777,true);
            }
            #生成临时图片
            $url = explode(',',$file);
            file_put_contents($this->filePath.$file_name, base64_decode($url[1]));
            #上传至OSS
            $res = $this->ossClient->uploadFile($this->bucket, $file_path,$this->filePath.$file_name);
            #检测图片是否上传成功
            $res =$this->ossClient->doesObjectExist($this->bucket, $file_path);
            if(!$res){
                $result['code'] = 90005;
                $result['msg'] = '上传图片出错';
            }else{
                #若OSS上传成功,则删除临时图片
                if(file_exists($this->filePath.$file_name)){
                    unlink($this->filePath.$file_name);
                }
                $result['code'] = 0;
                $result['msg'] = '上传成功'.$this->filePath.$file_name;
                $result['data']['img_path'] = $file_path;
            }
        }else{
            $result['code'] = 90005;
            $result['msg'] = '未选择图片存储位置';
        }
        return $result;
    }
    /**
     * 单图上传 已整合 FILE格式
     * @param $file  图片信息$_FILES
     * @param $type  类型
     * @return array
     */
    public function uploadImage($file,$type)
    {
        /************图片格式验证************/
        #允许的MIME类型
        $allow_mime = array('image/jpeg','image/png','image/gif','image/jpg');
        #允许的后缀名
        $allow_types = array('jpg','jpeg','png','gif');
        $file_info = pathinfo($file['name']);
        $file_px = getimagesize($file['tmp_name']);//限制图片像素
        if($file['error']){//判断错误号
            /*
           '0' =>  '没有错误发生，文件上传成功。'
           '1' => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。',
           '2' => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。',
           '3' => '文件只有部分被上传。',
           '4' => '没有文件上传。',
           '5' => '未能通过安全检查的文件。',
           '6' => '找不到临时文件夹。',
           '7' => '文件写入失败。',
           '8' => '文件类型不支持',
           '9' => '上传的临时文件丢失。',
           */
            switch($file['error']){
                case 1: $result['msg'] = '没有错误发生，文件上传成功'; break;
                case 2: $result['msg'] = '上传的文件超过了php.ini中upload_max_filesize的最大限制'; break;
                case 3: $result['msg'] = '文件只有部分被上传'; break;
                case 4: $result['msg'] = '没有文件上传'; break;
                case 5: $result['msg'] = '未能通过安全检查的文件'; break;
                case 6: $result['msg'] = '找不到临时文件夹'; break;
                case 7: $result['msg'] = '文件写入失败'; break;
                case 8: $result['msg'] = '找不到临时文件夹'; break;
                case 9: $result['msg'] = '上传的临时文件丢失'; break;
                default : $result['msg'] = '非法操作';
            }
            $result['code'] = 90005;
        }elseif(!in_array($file['type'],$allow_mime)){//判断MIME类型
            $result['code'] = 90005;
            $result['msg'] = '文件MIME类型不符';
        }elseif(!in_array($file_info['extension'],$allow_types)){//判断后缀名
            $result['code'] = 90005;
            $result['msg'] = '文件后缀不符';
        }elseif($file['size'] > 2000000){//判断文件大
            $result['code'] = 90005;
            $result['msg'] = '文件过大';
        }
        // elseif($file_px[0] > 128 || $file_px[1] >128){//判断像素
        //     $result['error'] = 90005;
        //     $result['msg'] = '图像分辨率超过128*128';
        // }
        else{
            switch($type){
                case 1: $dir = "article"; break;//文章
                case 2: $dir = "ad"; break;//广告
                default : $dir = '';
            }
            if($dir){
                $dir = $dir.'/'.date('Y',time()).'/'.date('m',time()).'/'.date('d',time());
                $file_name = md5(time().mt_rand(1000,9999).$file_info['filename']).'.'.$file_info['extension'];
                $file_path = $dir.'/'.$file_name;
                $file_temp = $file['tmp_name'];
                //echo config('oss.OSS_HOST').$file_path;
                $res = $this->ossClient->uploadFile($this->bucket, $file_path, $file_temp);
                //检测图片是否上传成功
                $res =$this->ossClient->doesObjectExist($this->bucket, $file_path);
                if(!$res){
                    $result['code'] = 90005;
                    $result['msg'] = '上传图片出错';
                }else{
                    $result['code'] = 0;
                    $result['msg'] = '上传成功';
                    $result['data']['img_path'] = $file_path;
                }
            }else{
                $result['code'] = 90005;
                $result['msg'] = '未选择图片存储位置';
            }
        }
        return $result;
    }

    /**
     * 删除图片 已整合
     * @param $img_path 图像完整地址
     * @return array
     */
    public function deleteImage($img_path)
    {
        #解析url
        $object = substr(parse_url($this->host . $img_path)['path'], 1);
        $this->ossClient->deleteObject($this->bucket, $object);
        $result['code'] = 0;
        $result['msg'] = '删除图片成功';
        return $result;
    }

}