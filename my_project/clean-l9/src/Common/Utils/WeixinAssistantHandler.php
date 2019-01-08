<?php

namespace Common\Utils;

use Common\Utils\ConfigHandler;

class WeixinAssistantHandler
{

    /*
     * 加载
     */
    static public function load()
    {
        if(! WeixinAssistantHandler::checkSignature())
        {
            exit();
        }
        
        WeixinAssistantHandler::processAction();
        
        echo $_GET["echostr"];
        exit();
    }

    /**
     * 检查签名
     * 
     * @return boolean
     */
    static public function checkSignature()
    {
        $tmpArr = array(
                ConfigHandler::getWeixinConfig("token"),
                $_GET["timestamp"],
                $_GET["nonce"] 
        );
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        return ($tmpStr == $_GET["signature"]);
    }

    static public function processAction()
    {
        $postStr = null;
        if(! $GLOBALS["HTTP_RAW_POST_DATA"])
        {
            $postStr = file_get_contents("php://input");
        }
        
        if(empty($postStr))
        {
            return false;
        }
        
        $postObj = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $msgtype = strtolower($postObj['MsgType']);
        $event = strtolower($postObj['Event']);
        $fromUsername = $postObj['FromUserName'];
        $toUsername = $postObj['ToUserName'];
        $keyword = trim($postObj['Content']);
        $time = time();
        
        if($keyword)
        {
            // 暂时不处理
            return true;
        }
        
        return false;
    }

    /*
     * 获取用户信息
     * @param $type :base：基础信息；info：详细信息；
     */
    static public function getWeixinUser($type = 'base')
    {
        $result = array();
        
        if($_REQUEST['state'] == 'callback')
        {
            $result = WeixinAssistantHandler::getOpenIdViaPageAuth($_REQUEST['code']);
            if($result->scope == 'snsapi_userinfo')
            {
                $result = WeixinAssistantHandler::getUserinfo($result->openid);
            }
        }
        else if($type == 'base')
        {
            WeixinAssistantHandler::cronWeixinUserBase();
        }
        else if($type == 'info')
        {
            WeixinAssistantHandler::cronWeixinUserInfo();
        }
        
        return $result;
    }

    /**
     * 获取用户基本信息CODE URL
     * 
     * @param type $state            
     */
    static public function cronWeixinUserBase($state = 'callback', $redirect_url = '')
    {
        $redirect_url = $redirect_url ? $redirect_url : WeixinAssistantHandler::getbackurl();
        header("Location:" . ConfigHandler::getWeixinConfig("getCodeUrl") . 'appid=' . ConfigHandler::getWeixinConfig("appId") . '&redirect_uri=' . urlencode($redirect_url) . '&response_type=code&scope=snsapi_base&state=' . $state . '#wechat_redirect');
        exit();
    }

    /**
     * 获取用户详细信息CODE URL
     * 
     * @param type $state            
     */
    static public function cronWeixinUserInfo($state = 'callback', $redirect_url = '')
    {
        $redirect_url = $redirect_url ? $redirect_url : WeixinAssistantHandler::getbackurl();
        header("Location:" . ConfigHandler::getWeixinConfig("getCodeUrl") . 'appid=' . ConfigHandler::getWeixinConfig("appId") . '&redirect_uri=' . urlencode($redirect_url) . '&response_type=code&scope=snsapi_userinfo&state=' . $state . '#wechat_redirect');
        exit();
    }

    /**
     * 网页授权-通过code换取网页授权access_token,openid
     */
    static public function getOpenIdViaPageAuth($code)
    {
        if(empty($code))
        {
            return false;
        }
        return json_decode(file_get_contents(ConfigHandler::getWeixinConfig("getAccessTokenUrl") . 'appid=' . ConfigHandler::getWeixinConfig("appId") . '&secret=' . ConfigHandler::getWeixinConfig("appSecret") . '&code=' . $code . '&grant_type=authorization_code'));
    }

    /**
     * 获取微信用户信息
     * 
     * @param type $openid
     *            subscribe / openid / nickname / sex /language / city / province / country / headimgurl / subscribe_time
     */
    static public function getUserinfo($openid)
    {
        return json_decode(file_get_contents(WeixinAssistantHandler::getUserinfoUrl($openid)));
    }

    /**
     * 获取用户信息 URL
     * 
     * @param type $openid            
     * @return type
     */
    static public function getUserinfoUrl($openid)
    {
        $access_token = WeixinAssistantHandler::getAccessToken();
        if(empty($access_token))
        {
            return false;
        }
        return ConfigHandler::getWeixinConfig('userInfoUrl') . '&access_token=' . $access_token . '&openid=' . $openid;
    }

    /**
     * 获取访问 AccessToken
     */
    static public function getAccessToken()
    {
        $result = json_decode(file_get_contents(ConfigHandler::getWeixinConfig('tokenUrl') . '&appid=' . ConfigHandler::getWeixinConfig('appId') . '&secret=' . ConfigHandler::getWeixinConfig('appSecret')));
        if($result && $result->access_token)
        {
            return $result->access_token;
        }
        else
        {
            return false;
        }
    }

    static public function getbackurl()
    {
        $protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        parse_str($_SERVER['QUERY_STRING'], $paramarray);
        $backurl = ! empty($paramarray['backurl']) ? $paramarray['backurl'] : $protocal . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $backurl;
    }
}