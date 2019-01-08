<?php

namespace Common\Utils\AliyunOSS;

include_once strval($_SERVER['DOCUMENT_ROOT']) . '/OSS/aliyun-php-sdk-core/Config.php';

use OSS\OssClient;
use Sts\Request\V20150401\AssumeRoleRequest;

class OssHandler
{
    private $ossClient;
    private $bucket;

    public function __construct($isInternal = false)
    {
        $keyData = self::getKeyData();
        $this->bucket = "service-robot";
        $endpoint = $isInternal ? "http://oss-internal.aliyuncs.com" : $keyData['OSS_ENDPOINT'];
        $this->ossClient = new OssClient($keyData['OSS_ACCESS_ID'], $keyData['OSS_ACCESS_KEY'], $endpoint, false);
    }

    static public function testGetBuceket()
    {
        
        // print_r($this->objectToArray(($this->ossClient->listObjects("service-robot"))));
        $options = array(
                'delimiter' => '/',
                'prefix' => 'robot/0413914900546000f853/2016/1/' 
        );
        
        // $refererConfig = new RefererConfig();
        // $refererConfig->setAllowEmptyReferer(true);
        // $refererConfig->addReferer("*.aliiyun.com");
        // $refererConfig->addReferer("*.*.aliiyuncs.com");
        // $this->ossClient->putBucketReferer(self::bucket, $refererConfig);
        
        // $object = $this->ossClient->signUrl(self::bucket,'pikaqiu.png');
        
        // $this->ossClient->deleteObject(self::bucket, 'file.php');
        $object = array();
        $oss = new OssHandler();
        $object = $oss->ossClient->listObjects($oss->bucket, $options);
        print_r($object);
        
        // $this->ossClient->deleteObjects(self::bucket, $object->getObjectList());
        
        exit();
    }

    /**
     * $uploadUrl 上传文件路径
     * $file 本地文件路径
     * 上传本地文件
     */
    static public function upload($uploadUrl, $file)
    {
        $oss = new OssHandler();
        
        return $oss->ossClient->uploadFile($oss->bucket, $uploadUrl, $file);
    }
    
    /**
     *  返回路径文件的元信息 
     *
     */
    
    static public function getObjectMeta($object)
    {
        $oss = new OssHandler();
        
        return $oss->ossClient->getObjectMeta($oss->bucket, $object);
    }
    
    /**
     * 获取文件夹的大小
     */
    
    static public function getDirSize($size=0,$uploadUrl)
    {
        $options = array(
                'delimiter' => '/',
                'prefix' => $uploadUrl
        );
        
        $objectList = self::getAllObjectKey($options);
        static $finalSize = 0;
//         print_r($objectList);
        while(null != $objectList->getPrefixList() )
        {
            if(null != $objectList->getPrefixList()->getPrefix())
            {
               $finalSize= self::getDirSize($size,$objectList->getPrefixList()->getPrefix());
            }
            else 
            {
                
            }
            
            print_r($objectList->getPrefixList());
        }
        

        if(count($objectList->getPrefixList())==0)
        {
              
            $finalSize += $size;
            
                var_dump(self::formatBytes($finalSize));
                return self::formatBytes($finalSize);
            
        }
        
        
        
    }

    /**
     * 删除存储在oss中的文件
     *
     * @param string $uploadUrl
     *            存储的key（文件路径和文件名）
     * @return
     *
     */
    static public function deleteObject($uploadUrl)
    {
        $oss = new OssHandler(); // 上传文件使用内网，免流量费
        
        return $oss->ossClient->deleteObject($oss->bucket, $uploadUrl);
    }

    /**
     * 返回bucket所有文件
     * 
     * @param
     *            list
     */
    static public function getAllObjectKey($options = null)
    {
        $oss = new OssHandler();
        
        return $oss->ossClient->listObjects($oss->bucket,$options);
    }

    /**
     * 生成预签名URL
     *
     * @param            
     *
     */
    static public function getUrl($uploadUrl)
    {
        $oss = new OssHandler();
        return $oss->ossClient->signUrl($oss->bucket, $uploadUrl);
    }

    /**
     * 判断object是否存在
     */
    static public function doesObjectExist($uploadUrl)
    {
        $oss = new OssHandler();
        return $oss->ossClient->doesObjectExist($oss->bucket, $uploadUrl);
    }

    /**
     * 对象转数组,使用get_object_vars返回对象属性组成的数组
     */
    private function objectToArray($array)
    {
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key => $value)
            {
                $array[$key] = $this->objectToArray($value);
            }
        }
        return $array;
    }

    /**
     * 取得aliyun OSS key 的数据
     *
     * @return string
     */
    static public function getKeyData()
    {
        $filename = dirname(strval($_SERVER['DOCUMENT_ROOT'])) . "/share/key/alipay_oss_key.json";
        $content = file_get_contents($filename);
        $configArr = json_decode($content, true);
        
        return $configArr;
    }

    /**
     * @param   $connectType  连接类型        1：机器      2：用户
     * @param   $param  机器SN码  或 用户ID
     * STS连接，返回key
     *
     * @return string
     */
    static public function getSTS($connectType,$param)
    {
        $content = self::read_file($_SERVER['DOCUMENT_ROOT'] . '/OSS/config.json');
        
        $myjsonarray = json_decode($content);
        $accessKeyID = $myjsonarray->AccessKeyID;
        $accessKeySecret = $myjsonarray->AccessKeySecret;
        $roleArn = $myjsonarray->RoleArn;
        $tokenExpire = $myjsonarray->TokenExpireTime;
        
        //$policy = self::read_file($_SERVER['DOCUMENT_ROOT'] . '/OSS/' . $myjsonarray->PolicyFile);
        
        //如果连接类型为机器
        if($connectType == 1)
        {
            $policy = '{"Statement": [{"Action": ["oss:*"],"Effect": "Allow","Resource": ["acs:oss:*:*:service-robot/robot/'.$param.'/*", "acs:oss:*:*:service-robot/robot/'.$param.'"]}],"Version": "1"}';
        }
        else if ($connectType == 2)
        {
            $policy = '{"Statement": [{"Action": ["oss:*"],"Effect": "Allow","Resource": ["acs:oss:*:*:service-robot/user/'.$param.'/*","acs:oss:*:*:service-robot/user/'.$param.'"]}],"Version": "1"}';
        }
        else if ($connectType == 3)
        {
            $policy = '{"Statement": [{"Action": ["oss:*"],"Effect": "Allow","Resource": ["acs:oss:*:*:service-robot/*", "acs:oss:*:*:service-robot"]}],"Version": "1"}';
        }
        
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyID, $accessKeySecret);
        $client = new \DefaultAcsClient($iClientProfile);
        
        $request = new AssumeRoleRequest();
        $request->setRoleSessionName("client_name");
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($tokenExpire);
              
        $response = $client->doAction($request);
        
        $rows = array();
        $body = $response->getBody();
        $content = json_decode($body);
        $rows['status'] = $response->getStatus();
        if($response->getStatus() == 200)
        {
            $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
            $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
            $rows['Expiration'] = $content->Credentials->Expiration;
            $rows['SecurityToken'] = $content->Credentials->SecurityToken;
        }
        else
        {
            $rows['AccessKeyId'] = "";
            $rows['AccessKeySecret'] = "";
            $rows['Expiration'] = "";
            $rows['SecurityToken'] = "";
        }
        
        return json_encode($rows);
    }

    static public function read_file($fname)
    {
        $content = '';
        
        if(! file_exists($fname))
        {
            echo "The file $fname does not exist\n";
            exit(0);
        }
        $handle = fopen($fname, "rb");
        while(! feof($handle))
        {
            $content .= fread($handle, 10000);
        }
        fclose($handle);
        return $content;
    }
    
    static public function formatBytes($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
    
    
}

?>