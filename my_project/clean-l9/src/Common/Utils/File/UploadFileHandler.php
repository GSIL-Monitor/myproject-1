<?php

namespace Common\Utils\File;

class UploadFileHandler
{
    private static $__defaultImageTypes = "|jpg|jpeg|png|bmp|gif|";
    private static $_defaultVideoTypes = "|mpeg|mp4|flv|avi|3gp|mov|";
    private static $__defaultFileTypes = "|doc|docx|ppt|pptx|xls|xlsx|zip|rar|txt|apk|ipa|dfu|bin|dll|rmf|csv|js|cleanpack|record|log|";
    private static $__defaultSize = 104857600;

    static public function requestUpload($name, $filePath, $isReName,$isScaleName=false)
    {
        if(empty($filePath))
        {
            return "@" . "路径错误";
        }
        
        // $filePath = $filePath . "/" . date("Y", time()) . "/" . date("m", time()) . "/" . date("d", time());
        //$filePath = $filePath . "/";
        
        $file = $_FILES[$name];

        
        if($file["error"] > 0)
        {
            return "@" . "文件上传错误！";
            // throw new \Exception($file["error"]);
        }
        
        $size = $file["size"];
        
        if($size <= 0)
        {
            return "@" . "文件上传错误！";
        }
        
        if(! self::checkFileSize($size))
        {
            return "@" . "文件大小超出限制";
        }
        
        $fileName = $file["name"];

        $fileArr = explode("_", $file["name"]);
        if($fileArr[0] && strlen($fileArr[0])>=10 )
        {   
            $filePath = $filePath . "/".$fileArr[0];
            if($fileArr[1]  &&  !is_numeric($fileArr[1]))
            {   
                if($fileArr[1] == "log")
                {   
                    $res[] = file_get_contents($file["tmp_name"]);
                    $res[] = $fileArr[0];
                    $res[] = "log";
                    return $res;
                }
                $filePath = $filePath . "/".$fileArr[1];

            }else
            {
                $filePath = $filePath . "/sweeping";
            }
            $temArr = $fileArr;
            unset($temArr[0]);
            $fileName = implode("_", $temArr);
        }

        if(! file_exists($filePath))
        {
            mkdir($filePath, 0755, true);
        }

        
        $type = pathinfo($fileName, PATHINFO_EXTENSION);

        if(! self::checkFileType($type))
        {
            return "@" . "不支持该文件类型";
        }
        if($type == "record")
        {
            $res = array("map"=>true);
        }

        //  清扫记录   添加数据库记录
        if ($type == 'txt') {
            $res['sn'] = $fileArr[0];
            $res['sort'] = $fileArr[1];
            $res['starttime'] = $fileArr[2];
            $res['endtime'] = $fileArr[3];
            $res['cleanarea'] = $fileArr[4];
            $tmp = explode('.', $fileArr[5]);
            $res['moparea'] = $tmp[0];
            $res['type'] = $type;
        }

        
        if($isReName)
        {
            if($isScaleName)
            {
                $length = getimagesize($file["tmp_name"])[0];
                $width = getimagesize($file["tmp_name"])[1];
                $fileName = self::getFileNameWithSize($fileName, $length, $width);
            }
            else 
            {
                //$fileName=self::getFileName($fileName);
            }
            
        }
        
        $fileName = $filePath . "/" . $fileName;



        move_uploaded_file($file["tmp_name"], iconv("UTF-8", "gb2312", $fileName));

        $res["fileName"] = $fileName;
        return $res;
    }
    
    /*
     * name 文件上传控件的name uploadType 1 任意 2.图片 3.视频 $author adair -update time:2015年8月4日 18:23:57
     */
    static public function requestUploadTypeFile($name, $filePath, $isReName,$isScaleName=false)
    {
        if(empty($filePath))
        {
            return "路径错误";
        }
        
        $uploadType = 1;
        
        $filePath = $filePath . "/" . date("Y", time()) . "/" . date("m", time()) . "/" . date("d", time());
        //$filePath = $filePath . "/";

        
        if(! file_exists($filePath))
        {
            mkdir($filePath, 0755, true);
        }
        
        $file = $_FILES[$name];
        if($file["error"] > 0)
        {
            //return "文件上传错误！";
             throw new \Exception($file["error"]);
        }
        
        $size = $file["size"];
        
        if($size <= 0)
        {
            return "@" ."文件上传错误！";
        }
        
        if(! self::checkFileSize($size))
        {
            return "@" ."文件大小超出限制";
        }
        
        $fileName = $file["name"];
        
        $type = pathinfo($fileName, PATHINFO_EXTENSION);
        
        if(! self::checkFileType($type))
        {
            return "@" ."不支持该文件类型";
        }
        if(self::checkImageType($type))
        {
            $uploadType = 2;
        }
        if(self::checkVideoType($type))
        {
            $uploadType = 3;
        }
        
        $fileName = $file["name"];
        $sourceFileName = $fileName;
        if($isReName)
        {
            if($isScaleName)
            {
                $length = getimagesize($file["tmp_name"])[0];
                $width = getimagesize($file["tmp_name"])[1];
                $fileName = self::getFileNameWithSize($fileName, $length, $width);
            }
            else
            {
                $fileName=self::getFileName($fileName);
            }
        }
        
        $fileName = $filePath . "/" . $fileName;
        move_uploaded_file($file["tmp_name"], iconv("UTF-8", "gb2312", $fileName));
        return array(
                "fileName" => $fileName,
                "filename" => $fileName,
                "fileType" => $type,
                "fileSize" => $size,
                "uploadType" => $uploadType,
                "sourceFileName" => $sourceFileName 
        );
    }

    static private function getFileName($fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        

        $fileName = date("YmdGis", time()) . strval(rand(100000, 999999)) . "." . $ext;
        return $fileName;
    }
    
    // 新增方法，加入图片的长和宽
    static private function getFileNameWithSize($fileName, $length, $width)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        
        $fileName = date("YmdGis", time()) . strval(rand(100000, 999999)) . "_" . $length . "_" . $width . "." . $ext;
        return $fileName;
    }

    static private function checkFileSize($size)
    {
        if($size > self::$__defaultSize)
        {
            return false;
        }
        return true;
    }

    static private function checkImageType($type)
    {
        $type = "|" . strtolower($type) . "|";
        
        if(strpos(self::$__defaultImageTypes, $type) !== false)
        {
            return true;
        }
        return false;
    }

    static private function checkVideoType($type)
    {
        $type = "|" . strtolower($type) . "|";
        
        if(strpos(self::$_defaultVideoTypes, $type) !== false)
        {
            return true;
        }
        return false;
    }

    static private function checkFileType($type)
    {
        $type = "|" . strtolower($type) . "|";
        if(strpos(self::$__defaultImageTypes . self::$_defaultVideoTypes . self::$__defaultFileTypes, $type) !== false)
        {
            return true;
        }
        return false;
    }
}
?>