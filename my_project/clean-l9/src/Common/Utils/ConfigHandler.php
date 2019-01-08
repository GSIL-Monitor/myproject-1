<?php

namespace Common\Utils;

include_once strval($_SERVER['DOCUMENT_ROOT']) . '/SysConfig.php';
use Web\System\SysConfig;
use Common\Utils\CodeCommentHandler;

class ConfigHandler
{

    static public function getConfigDir()
    {
        $dir = SysConfig::SHAREDIR;
        
        
        if(empty($dir))
        {
            $dir = dirname(strval($_SERVER['DOCUMENT_ROOT'])) . "/share/config";
        }
        else
        {
            $dir = dirname(strval($_SERVER['DOCUMENT_ROOT'])) . "/" . $dir . "/config";
        }
        
        return $dir;
    }

    static public function getCommonConfig($key)
    {
        return ConfigHandler::getConfigByKey($key, "common");
    }

    static public function getWeixinConfig($key)
    {
        return ConfigHandler::getConfigByKey($key, "weixin");
    }

    static public function getSessionConfig()
    {
        return ConfigHandler::getConfig("session");
    }

    static public function getJsonConfigContent($fileName)
    {
        // $filename=strval($_SERVER['DOCUMENT_ROOT'])."/config/".SysConfig::ENVIREMENT."/adminFeature.json";
        // $content=file_get_contents($fileName);
        $con = new CodeCommentHandler($fileName);
        return $con->compact();
    }

    static public function getJsonConfigArr($fileName)
    {
        $content = self::getJsonConfigContent($fileName);
        return json_decode($content, true);
    }

    static public function getConfigByKey($key, $configName = "")
    {
        $configArr = ConfigHandler::getConfig($configName);
        return $configArr[$key];
    }

    static public function getConfig($configName = "")
    {
        $filename = self::getConfigDir() . "/" . SysConfig::ENVIREMENT . "/" . ($configName ? $configName : "common") . ".json";
        
        $content = file_get_contents($filename);
        $configArr = json_decode($content, true);
        
        return $configArr;
    }
}
