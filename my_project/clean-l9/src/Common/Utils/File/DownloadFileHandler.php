<?php

namespace Common\Utils\File;

class DownloadFileHandler
{

    static public function requestDownload($filePath)
    {
        $separateFilePath = explode('/', $filePath);
        $fileName = end($separateFilePath);
        
        if(! file_exists($filePath))
        {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
        }
        else
        {
            $file = fopen($filePath, "r");
            header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . filesize($filePath));
            header("Content-Disposition: attachment; filename=" . $fileName);
            echo fread($file, filesize($filePath));
            @flush();
            @ob_flush();
            fclose($file);
        }
        exit();
    }
}
?>