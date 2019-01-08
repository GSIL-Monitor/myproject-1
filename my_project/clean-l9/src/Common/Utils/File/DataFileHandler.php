<?php

namespace Common\Utils\File;

include_once strval($_SERVER['DOCUMENT_ROOT']) . '/SysConfig.php';
use Web\System\SysConfig;

class DataFileHandler
{

    static public function getFileToArray($fileName, $filePath = null)
    {
        if(empty($filePath))
        {
            $fileName = self::getDataDir() . "/" . $fileName;
        }
        else
        {
            $fileName = $filePath . "/" . $fileName;
        }
        $content = file_get_contents($fileName);
        $dataArr = json_decode($content, true);
        return $dataArr;
    }

    static public function getFileContent($fileName, $filePath = null)
    {
        if(empty($filePath))
        {
            $fileName = self::getDataDir() . "/" . $fileName;
        }
        else
        {
            $fileName = $filePath . "/" . $fileName;
        }
        $content = file_get_contents($fileName);
        return $content;
    }

    static public function getDataDir()
    {
        $dir = SysConfig::SHAREDIR;
        
        if(empty($dir))
        {
            $dir = dirname(strval($_SERVER['DOCUMENT_ROOT'])) . "/share/data";
        }
        else
        {
            $dir = dirname(strval($_SERVER['DOCUMENT_ROOT'])) . "/" . $dir . "/data";
        }
        return $dir;
    }

    static public function writeDataToFlie($content, $fileName, $filePath = null)
    {
        if(empty($filePath))
        {
            $fileName = strval($_SERVER['DOCUMENT_ROOT']) . "/data/" . $fileName;
        }
        else
        {
            $fileName = $filePath . "/" . $fileName;
        }
        $fp = fopen($fileName, "w");
        fwrite($fp, $content);
        fclose($fp);
    }
}
