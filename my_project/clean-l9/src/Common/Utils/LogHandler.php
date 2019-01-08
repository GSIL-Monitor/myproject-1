<?php

namespace Common\Utils;

use Common\Utils\ConfigHandler;

class LogHandler
{

    static public function writeLog($msg, $subDir = "")
    {
        $filePath = self::getFilePath();
        if(! empty($subDir))
        {
            $filePath = $filePath . "/" . $subDir;
        }
        if(! file_exists($filePath))
        {
            mkdir($filePath, 0777, true);
        }
        
        $fileName = $filePath . "/" . self::getFileName();
        $fp = fopen($fileName, "a");
        fwrite($fp, $msg);
        fclose($fp);
    }

    static private function getFileName()
    {
        $fileName = date("Ymd", time()) . ".txt";
        return $fileName;
    }

    static private function getFilePath()
    {
        return ConfigHandler::getCommonConfig("logPath");
    }
}

?>