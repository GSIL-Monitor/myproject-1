<?php

namespace Common\Utils\File;

class PHPExcelHandler
{

    static public function getFileToArray($fileName, $hasHead = false)
    {
        if(empty($fileName))
        {
            return null;
        }
        
        $fileType = \PHPExcel_IOFactory::identify($fileName); // 文件名自动判断文件类型
        $objReader = \PHPExcel_IOFactory::createReader($fileType);
        $objPHPExcel = $objReader->load($fileName);
        
        $currentSheet = $objPHPExcel->getSheet(0); // 第一个工作簿
        $allRow = $currentSheet->getHighestRow(); // 行数
        $startRowIndex = 1;
        if($hasHead)
        {
            $startRowIndex += 1;
        }
        $allColumn = $currentSheet->getHighestColumn(); // 列数
        
        $result = array();
        
        for($currentRow = $startRowIndex; $currentRow <= $allRow; $currentRow ++)
        {
            $columnArr = array();
            for($currentColumn = 'A'; ord($currentColumn) <= ord($allColumn); $currentColumn ++)
            {
                $cellName = $currentColumn . $currentRow;
                $val = $currentSheet->getCell($cellName)->getValue();
                array_push($columnArr, $val);
            }
            if(! self::isEmptyRow($columnArr))
            {
                array_push($result, $columnArr);
            }
        }
        return $result;
    }

    static private function isEmptyRow($columArr)
    {
        if(empty($columArr))
        {
            return true;
        }
        for($i = 0; $i < count($columArr); $i ++)
        {
            if(! empty($columArr[$i]))
            {
                return false;
            }
        }
        return true;
    }

    static public function writeArrToFlie($content, $fileName, $filePath = null)
    {
    }
}
