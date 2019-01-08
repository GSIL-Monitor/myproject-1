<?php

namespace Common\Utils;

use Common\Utils\File\DataFileHandler;

class IPHandler
{

    static public function getClientIP()
    {
        $ip = "Unknow";
        if(getenv("HTTP_CLIENT_IP"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        else if(getenv("HTTP_X_FORWARDED_FOR"))
        {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        else if(getenv("REMOTE_ADDR"))
        {
            $ip = getenv("REMOTE_ADDR");
        }
        return $ip;
    }

    static public function getIPBlacklist()
    {
        $result = array();
        $filename = DataFileHandler::getDataDir() . "/ipBlacklist.json";
        $handle = fopen($filename, 'r');
        while(! feof($handle))
        {
            array_push($result, fgets($handle));
        }
        return $result;
    }

    static public function checkIsBlackIP($ip)
    {
        $blacklist = self::getIPBlacklist();
        if(! empty($blacklist) && ! empty($ip))
        {
            $ip = trim($ip);
            // return in_array($ip, $blacklist);
            for($i = 0; $i < count($blacklist); $i ++)
            {
                if(self::compareIPs($ip, $blacklist[$i]))
                {
                    return true;
                }
            }
        }
        return false;
    }

    static private function compareIPs($sourceIp, $blackIp)
    {
        if(empty($sourceIp) || empty($blackIp))
        {
            return false;
        }
        
        $sourceArr = explode(".", $sourceIp);
        $blackArr = explode(".", $blackIp);
        if(count($sourceArr) != count($blackArr))
        {
            return false;
        }
        
        for($i = 0; $i < count($blackArr); $i ++)
        {
            if($sourceArr[$i] != $blackArr[$i] && $blackArr[$i] != "*")
            {
                return false;
            }
        }
        return true;
    }
}
?>