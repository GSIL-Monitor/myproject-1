<?php

namespace Common\Utils\File;

class ImageMagickHandler
{

    /*
     * resizeType 1.原尺寸压缩 2.等比例压缩 3.中心裁剪压缩
     */
    static public function Resize($srcImg, $targetDir, $resizeType, $quality, $targetWidth = 0, $targetHeight = 0, $nameFlag = "", $isEnlarge = FALSE)
    {
        $imObj = new \Imagick();
        $imObj->readimage($srcImg);
        // 获取图片原始高宽
        $srcSize = getimagesize($srcImg);
        $srcWidth = $srcSize[0];
        $srcHeight = $srcSize[1];
        // 转换参数为相应类型
        $targetWidth = intval($targetWidth);
        $targetHeight = intval($targetHeight);
        $resizeType = intval($resizeType);
        
        // 对于压缩尺寸大于原尺寸的处理
        if(! $isEnlarge)
        {
            $srcScale = floatval($srcWidth) / $srcHeight;
            $targeScale = floatval($targetWidth) / $targetHeight;
            if($targeScale > $srcScale && $targetWidth > $srcWidth)
            {
                $targetHeight = $srcHeight;
                $targetWidth = $targetHeight * $targeScale;
            }
            else if($targeScale < $srcScale && $targetHeight > $srcHeight)
            {
                $targetWidth = $srcWidth;
                $targetHeight = $targetWidth / $targeScale;
            }
        }
        
        // 生成新文件路径
        $targetFileName = "";
        if(! empty($nameFlag))
        {
            $targetFileName = self::getFlagFileName($srcImg, $nameFlag);
        }
        else
        {
            $targetFileName = self::getFileName($srcImg, $targetDir);
        }
        
        // 设置压缩质量
        $imObj->setimagecompressionquality(($imObj->getimagecompressionquality()) * $quality);
        $imObj->stripImage();
        // $imObj->writeimage($targetFileName);
        
        // $imObj=new \Imagick($targetFileName);
        // 根据压缩类型进行不同的处理
        if($resizeType == 1)
        {
            // 按原尺寸进行体积压缩
            $imObj->resizeimage($srcWidth, $srcHeight, \Imagick::FILTER_CATROM, 1);
        }
        else if($resizeType == 2)
        {
            // 根据输入高宽等比例进行尺寸压缩 若输入尺寸的比例与原图片比例不一致则按最长边算 比如原图100*50 输入50*50 则压缩后为50*25
            if($targetWidth > 0 && $targetHeight <= 0)
            {
                $targetHeight = ($srcHeight / $srcWidth) * $targetWidth;
                $targetHeight = round($targetHeight);
            }
            else if($targetWidth <= 0 && $targetHeight > 0)
            {
                $targetWidth = ($srcWidth / $srcHeight) * $targetHeight;
                $targetWidth = round($targetWidth);
            }
            $imObj->resizeimage($targetWidth, $targetHeight, \Imagick::FILTER_CATROM, 1, true);
        }
        else if($resizeType == 3)
        {
            // 根据输入高宽从中心进行裁剪
            $imObj->cropthumbnailimage($targetWidth, $targetHeight);
        }
        $imObj->writeimage($targetFileName);
        
        // 获取缩略图高宽
        $targetSize = getimagesize($targetFileName);
        $targetWidth = $targetSize[0];
        $targetHeight = $targetSize[1];
        
        $result = array(
                "filename" => $targetFileName,
                "sourceWidth" => $srcWidth,
                "sourceHeight" => $srcHeight,
                "width" => $targetWidth,
                "height" => $targetHeight 
        );
        
        return $result;
    }

    static private function getFileName($srcImg, $targetDir)
    {
        $ext = pathinfo($srcImg, PATHINFO_EXTENSION);
        
        $fileName = $targetDir . "/" . date("Y", time()) . "/" . date("m", time()) . "/" . date("YmdGis", time()) . strval(rand(100000, 999999)) . "_." . $ext;
        return $fileName;
    }

    static private function getFlagFileName($srcImg, $flag)
    {
        $ext = pathinfo($srcImg, PATHINFO_EXTENSION);
        $fileName = pathinfo($srcImg, PATHINFO_DIRNAME) . "/" . pathinfo($srcImg, PATHINFO_FILENAME) . $flag . "." . $ext;
        return $fileName;
    }
}


