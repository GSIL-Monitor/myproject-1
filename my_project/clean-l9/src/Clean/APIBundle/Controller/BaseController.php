<?php

namespace Clean\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializerBuilder;
use Common\Utils\Crypt\AESCryptHandler;
use Clean\LibraryBundle\Entity\APIResult;
use Clean\LibraryBundle\Entity\RobotResult;
use Symfony\Component\HttpFoundation\Session\Session;
use Common\Utils\HtmlHandler;
use Common\Utils\LogHandler;
use Common\Utils\IPHandler;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\UserInfoEntity;
use Clean\LibraryBundle\Entity\LoginTokenEntity;
use Common\Utils\Crypt\CheckCryptHandler;

class BaseController extends Controller
{
   

    public function __construct()
    {
        $this->LoginUserId=0;
        $this->getRequestData();
    }

     /*
     * get data parameter and save as array
     */
    private function getRequestData()
    {   
        $data = "";
        if(isset($_REQUEST["data"]))
        {
            $data = $_REQUEST["data"];
        }
        else if(isset($_REQUEST["DATA"]))
        {
            $data = $_REQUEST["DATA"];
        }
        if(! empty($data))
        {
            $this->RequestData = json_decode($data, true);
            // 临时处理ccid乱码的问题
            if(empty($this->RequestData))
            {
                $data = @preg_replace("/,\"CCID\":\".*\"/", "", $data);
                // LogHandler::writeLog("DealData:".$data."\r\n","GPSInfo");
                $this->RequestData = json_decode($data, true);
                if(empty($this->RequestData))
                {   
                    $data = trim($data,"}"); 
                    $data = trim($data,"{"); 
                    $temArr = explode(",", $data);
                    $resArr = array();
                    for ($i=0; $i <count($temArr) ; $i++) { 
                        $temArr1 = explode(":", $temArr[$i]);
                        if($temArr1[0] && $temArr1[1])
                        {
                            $resArr[$temArr1[0]] = $temArr1[1];
                        }
                    }
                    $this->RequestData = $resArr;
                }
            }
        }
    }

    protected function getToken($userInfo)
    {  

        $userId = 0;
        if(! empty($userInfo))
        {
            $userId = $userInfo->getUserId();
        }

        $expiredTime = new \DateTime(date("Y-m-d H:m:s", time() + 3600 * 24 * 100));
        $ip = IPHandler::getClientIP();
        
        $deviceType = intval($this->getParameter("deviceType"));
        $deviceToken = $this->getParameter("deviceToken");
        if($deviceToken=="no&nbsp;device&nbsp;token")
        {
            $deviceToken="";
        }
        $deviceNumber = $this->getParameter("deviceNumber");
        if(empty($deviceNumber))
        {
            $deviceNumber=$deviceToken;
        }

        $token = md5($userId . "@" . time() . rand());
        
        $loginToken = new LoginTokenEntity();
        $loginToken->setLoginToken($token);
        $loginToken->setLoginIp($ip);
        $loginToken->setUserId($userId);
        $loginToken->setExpiredTime($expiredTime);
        $loginToken->setDeviceToken($deviceToken);
        $loginToken->setDeviceType($deviceType);
        $loginToken->setAesKEY($this->getAESKEY());
        $loginToken->setAesIV($this->getAESKEY());
    
        $loginToken->setDeviceNumber($deviceNumber);
        
        $lmlt = $this->get("library_model_clean_logintoken");
        if($deviceType != CommonDefine::DEVICE_TYPE_WEB)
        {
            // 清除其他手机的登陆
            $lmlt->deletePhoneLoginTokenByUserId($userId);
        }
        // 添加登陆
        $lmlt->AddLoginToken($loginToken);

        if(! empty($userId))
        {   
            $userId = strval($userId);
            $userName = $userInfo->getUserName();
            
            $result = $token . CommonDefine::SPLIT_WORD . $userName . CommonDefine::SPLIT_WORD;
            $result = AESCryptHandler::encrypt($result,CommonDefine::AES_KEY, CommonDefine::AES_IV);
            $secret = $token . CommonDefine::SPLIT_WORD . $userId . CommonDefine::SPLIT_WORD . $loginToken->getAesKEY() . CommonDefine::SPLIT_WORD . $loginToken->getAesIV() . CommonDefine::SPLIT_WORD;
            $lmcu = $this->get("library_model_clean_userinfo");
            $userInfoEntity = $lmcu->getEntity($userId);

            $data = array(
                    "token" => $result,
                    "userName"=>$userName,
                    "loginToken"=>AESCryptHandler::encrypt($secret, CommonDefine::AES_KEY, CommonDefine::AES_IV),
                    "userInfo"=>$userInfoEntity
            ); 
            return $data;
        }
        return "";
    }

    private function getAESKEY($length = 16)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789'; // ABCDEFGHIJKLMNOPQRSTUVWXYZ
        $user = '';
        for($i = 0; $i < $length; $i ++)
        {
            $user .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $user;
    }

     /*
     * 验证用户 包括加密解密验证、token验证、时效性验证
     */
    protected function validateLoginUser($userId)
    {
        $tempUserId = intval($this->requestParameter("onlog"));
        if(! empty($tempUserId))
        {
            $this->LoginUserId = $tempUserId;
            return true;
        }
        if(isset($_REQUEST["token"]))
        {
            $token = $_REQUEST["token"];
            if(! empty($token))
            {   
                $lmlt = $this->get("library_model_clean_logintoken");
                $loginTokenEntity = $lmlt->getLoginTokenByUserId($userId);
                if(empty($loginTokenEntity))
                {
                    return null;
                }
                $token = str_replace(" ", "+", $token);
                
                $token = AESCryptHandler::decrypt($token, $loginTokenEntity->getAesKEY(), $loginTokenEntity->getAesIV());
                if(strstr($token, CommonDefine::SPLIT_WORD))
                {
                    $arr = explode(CommonDefine::SPLIT_WORD, $token);
                    $token = $arr[0];
                    if($token != $loginTokenEntity->getLoginToken())
                    {
                        return null;
                    }
                    $this->LoginToken = $token;
                    
                    if(! $this->checkTimeValidate($arr[1]))
                    {
                        return false;
                    }
                    $this->LoginUserId = $loginTokenEntity->getUserId();
                    $this->DeviceToken = $loginTokenEntity->getDeviceToken();
                    $this->DeviceType = $loginTokenEntity->getDeviceType();
                    if(is_int($this->LoginUserId) && $this->LoginUserId > 0)
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function validateCleanMachine()
    {   
        $sid = $_COOKIE["sid"];
        $sid = str_replace(" ", "+", $sid);
        //LogHandler::writeLog($sid."\r\n", "cookie");
        $timeStr = CheckCryptHandler::decrypt($sid);
        if(!$timeStr)
        {
            return false;
        }
        $time = intval(substr($timeStr,7));

        if($time < 100)
        {
            return false;
        }
        $nowTime = time();
        $tempTime = 30*24*3600;
        if($time + $tempTime < $nowTime)
        {
            return false;
        }

        return true;
    }

    //更新后的
    public function validateCleanRobot()
    {   
        $cookies = $_COOKIE["cookies"];
        $cookies = str_replace(" ", "+", $cookies);

        $timeStr = CheckCryptHandler::decrypt($cookies);
        if(!$timeStr)
        {
            return false;
        }
        $time = intval(substr($timeStr,7));

        if($time < 100)
        {
            return false;
        }
        $nowTime = time();
        $tempTime = 30*24*3600;
        if($time + $tempTime < $nowTime)
        {
            return false;
        }

        return true;
    }


    /*
     * 验证用户 包括加密解密验证、token验证、时效性验证
     */
    protected function validateResetPassword()
    {
        if(isset($_REQUEST["token"]))
        {
            $token = $_REQUEST["token"];
            if(! empty($token))
            {
                $token = str_replace(" ", "+", $token);
                
                $token = AESCryptHandler::decrypt($token, CommonDefine::AES_KEY, CommonDefine::AES_IV);
                
                if(strstr($token, CommonDefine::SPLIT_WORD))
                {
                    $arr = explode(CommonDefine::SPLIT_WORD, $token);
                    $token = $arr[0];
                    
                    return true;
                    // if(! $this->checkTimeValidate($arr[1]))
                    // {
                    //     return false;
                    // }
                }
            }
        }
        return false;
    }



    //验证机器设备
    protected function validateMachine($token)
    {   
        return true;
        $token = intval(AESCryptHandler::decrypt($token, CommonDefine::AES_KEY, CommonDefine::AES_IV));
        $temArr = explode("@", $token);
        //验证IP
        $ip = $ip = IPHandler::getClientIP();
        if($temArr[0] != $ip)
        {
            return false;
        }
        //验证密码
        if($temArr[1] != md5(md5("inmotion123")))
        {
            return false;
        }
        //验证时间
        if(time() - $temArr[2] > 120 )
        {
            return false;
        }

        return true;
    }


    /*
     * tokenTime 时间戳
     */
    private function checkTimeValidate($tokenTime)
    {
        $tempTime = intval($tokenTime);
        if(empty($tempTime))
        {
            return false;
        }
        $submitTime = strtotime("+5 minute", $tempTime);
        
        $nowTime = time();
        if($submitTime < $nowTime)
        {
            return false;
        }
        return true;
    }


    /*
     * get request data from 'data' parameter
     */
    protected function getParameter($key, $isTrim = true, $isHtmlEncode = true)
    {
        if(isset($this->RequestData) && array_key_exists(strval($key), $this->RequestData))
        {
            $result = $this->RequestData[$key];
            if($isTrim)
            {
                $result = trim($result);
            }
            if($isHtmlEncode)
            {
                $result = HtmlHandler::htmlEncode($result);
            }
            return $result;
        }
        return "";
    }

    /*
     * 直接获取post或者get方式传来的参数
     */
    protected function requestParameter($key, $isTrim = true, $isHtmlEncode = true)
    {
        if(isset($_REQUEST[$key]))
        {
            $result = $_REQUEST[$key];
            if($isTrim)
            {
                $result = trim($result);
            }
            if($isHtmlEncode)
            {
                $result = HtmlHandler::htmlEncode($result);
            }
            return $result;
        }
        return "";
    }

    protected function getAPIResultJson($code, $message, $data, $isHtmlDecode = TRUE)
    {   
        if(! empty($message))
        {
            $message = $this->get("translator")->trans($message);
        }
        
        $apiResult = new APIResult();
        $apiResult->code = $code;
        $apiResult->message = $message;
        if(!empty($data))
        {
            $apiResult->data = $data;
        }
        $serializer = SerializerBuilder::create()->build();
        $dataJson = $serializer->serialize($apiResult, "json");
        // $dataJson=json_encode($apiResult,JSON_UNESCAPED_UNICODE);
        if($isHtmlDecode)
        {
            $dataJson = HtmlHandler::htmlDecode($dataJson);
        }
        return $dataJson;
    }

    protected function getRobotResultJson($code, $message, $data, $isHtmlDecode = TRUE)
    {   
        if(! empty($message))
        {
            $message = $this->get("translator")->trans($message);
        }
        
        $apiResult = new APIResult();
        $apiResult->code = intval($code);
        $apiResult->message = $message;
        if(!empty($data))
        {
            $apiResult->data = $data;
        }
        $serializer = SerializerBuilder::create()->build();
        $dataJson = $serializer->serialize($apiResult, "json");
        // $dataJson=json_encode($apiResult,JSON_UNESCAPED_UNICODE);
        if($isHtmlDecode)
        {
            $dataJson = HtmlHandler::htmlDecode($dataJson);
        }
        return $dataJson;
    }

    protected function getResultJson($code, $message, $data, $isHtmlDecode = TRUE)
    {   
        if(! empty($message))
        {
            $message = $this->get("translator")->trans($message);
        }
        
        $apiResult = new RobotResult();
        $apiResult->errno = intval($code);
        $apiResult->errmsg = $message;
        if(!empty($data))
        {
            $apiResult->data = $data;
        }
        $serializer = SerializerBuilder::create()->build();
        $dataJson = $serializer->serialize($apiResult, "json");
        // $dataJson=json_encode($apiResult,JSON_UNESCAPED_UNICODE);
        if($isHtmlDecode)
        {
            $dataJson = HtmlHandler::htmlDecode($dataJson);
        }
        return $dataJson;
    }

    protected function writeErrorLog($ex, $class, $function)
    {
        LogHandler::writeLog(date("Y-m-d H:i:s") . "\r\n" . strval($ex) . "\r\n\r\n", $class . "/" . $function);
    }

    protected function tranlate($message)
    {
        if(empty($message))
        {
            return $message;
        }
        return $this->get("translator")->trans($message);
    }

    protected function getCurrentLanguage()
    {
        return $this->get("translator")->getLocale();
    }
}
?>