<?php

namespace Common\Utils\System;

use Common\Utils\ConfigHandler;

class ThreadHandler
{

    static public function ThreadFsockopen($url, $requestType = "GET", $paramArr = null)
    {
        $host = ConfigHandler::getCommonConfig("host");
        $port = ConfigHandler::getCommonConfig("port");
        
        $data = self::getDataFromArray($paramArr);
        
        // curl start
        // $ch = curl_init();
        
        // $curl_opt = array(CURLOPT_URL, "$url?$data",
        // CURLOPT_RETURNTRANSFER, 1,
        // CURLOPT_TIMEOUT, 1,);
        
        // curl_setopt_array($ch, $curl_opt);
        
        // curl_exec($ch);
        
        // curl_close($ch);
        // curl end
        
        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        if(! $fp)
        {
            return "$errstr ($errno)";
        }
        else
        {
            $out = "";
            
            if(! empty($requestType) && strtoupper($requestType) == "POST")
            {
                $out .= "$requestType $url HTTP/1.1\r\n";
                $out .= "Host:$host:$port\r\n";
                $out .= "Content-type: application/x-www-form-urlencoded\r\n";
                $out .= "Content-length: " . strlen($data) . "\r\n";
                $out .= $data . "\r\n";
            }
            else
            {
                $out .= "$requestType $url?$data HTTP/1.1\r\n";
                $out .= "Host:$host:$port\r\n";
            }
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            /*
             * 忽略执行结果
             * while (!feof($fp)) {
             * //fgets($fp);
             * //LogHandler::writeLog(fgets($fp));
             * }//
             */
            usleep(10000);
            fclose($fp);
        }
    }

    static private function getDataFromArray($paramArr)
    {
        if(empty($paramArr) || ! is_array($paramArr))
        {
            return "";
        }
        
        $paramCount = count($paramArr);
        
        $result = "";
        foreach($paramArr as $key => $val)
        {
            $result .= $key . "=" . urlencode($val) . "&";
        }
        $result = trim($result, '&');
        return $result;
    }
}
