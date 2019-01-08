<?php

namespace Common\Utils\File;

class FileCommonHandler
{   
     static function deleteDir($dir)
    {
        if(!file_exists($dir))
        {
            return;
        }
        // 先删除目录下的文件：
        $dh = opendir($dir);
        $file = readdir($dh);
        while($file!==false)
        {
            if($file != "." && $file != "..")
            {
                $fullpath = $dir . "/" . $file;
                if(! is_dir($fullpath))
                {
                    @unlink($fullpath);
                }
                else
                {
                    self::deleteDir($fullpath);
                }
            }
            $file = readdir($dh);
        }
        
        closedir($dh);
        // 删除当前文件夹：
        if(rmdir($dir))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    static function deleteFiles($dir)
    {
        if(!file_exists($dir))
        {
            return;
        }
        // 先删除目录下的文件：
        $dh = opendir($dir);
        $file = readdir($dh);
        while($file!==false)
        {
            if($file != "." && $file != "..")
            {
                $fullpath = $dir . "/" . $file;
                if(! is_dir($fullpath))
                {
                    @unlink($fullpath);
                }
                else
                {
                    self::deleteDir($fullpath);
                }
            }
            $file = readdir($dh);
        }
    
        closedir($dh);
    }
    
    /*
     * 获取文件列表
     */
    static function getFiles($dir)
    {
        $files = array();
        $dirpath = realpath($dir);
        $filenames = scandir($dir);
    
        foreach ($filenames as $filename)
        {
            if ($filename=='.' || $filename=='..')
            {
                continue;
            }
    
            $file = $dirpath . DIRECTORY_SEPARATOR . $filename;
             
            if (is_dir($file))
            {
                $files = array_merge($files, self::getFiles($file));
            }
            else
            {
                $files[] = $file;
            }
        }
    
        return $files;
    }

    static public function getFromFile($filename)
    {
        if(! file_exists($filename))
        {
            return null;
        }
        $content = file_get_contents($filename);
        return $content;
    }

    static public function writeToFlie($content, $filename)
    {
        $filePath = dirname($filename);
        if(! file_exists($filename))
        {
            mkdir($filePath, 0777, true);
        }
        $fp = fopen($filename, "w");
        fwrite($fp, $content);
        fclose($fp);
    }

    static public function getFilesFromDir($dir)
    {
        if(! file_exists($dir))
        {
            return null;
        }
        
        $result = array();
        $filenames = scandir($dir);
        foreach($filenames as $name)
        {
            if($name != "." && $name != "..")
            {
                array_push($result, $name);
            }
        }
        return $result;
    }
}
