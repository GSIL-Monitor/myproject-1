<?php

namespace Common\Utils;

use Common\Utils\File\DataFileHandler;

class GPSHandler
{
    // static public function getGPSCoordinateByNet($lat,$lng)
    // {
    // if(empty($lat) || empty($lng))
    // {
    // return "";
    // }
    // $url="http://api.zdoz.net/gcj2wgs.aspx?lat=".$lat."&lng=".$lng;
    // $content=file_get_contents($url);
    // if(!empty($content))
    // {
    // $dataArr=json_decode($content,true);
    // return $dataArr;
    // }
    // return "";
    // }
    public function translateGPSToBaidu($lat, $lng)
    {
    }

    /**
     * @brief 计算SN签名算法
     * 
     * @param string $ak
     *            access key
     * @param string $sk
     *            secret key
     * @param string $url
     *            url值，例如: /geosearch/nearby 不能带hostname和querstring，也不能带？
     * @param array $querystring_arrays
     *            参数数组，key=>value形式。在计算签名后不能重新排序，也不能添加或者删除数据元素
     * @param string $method
     *            只能为'POST'或者'GET'
     */
    public function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if($method === 'POST')
        {
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url . '?' . $querystring . $sk));
    }
    
    // GPGGA坐标转换为正常的GPS坐标
    static public function translateGPGGAToGPS($coordinate, $direction)
    {
        if(empty($coordinate))
        {
            return $coordinate;
        }
        $result = $coordinate;
        if($direction == "N" || $direction == "S")
        {
            $tempCorrdinate = strval($coordinate);
            $result = substr($tempCorrdinate, 0, 2);
            $minute = substr($tempCorrdinate, 2);
            $result = floatval($result) + floatval($minute / 60.0);
        }
        else if($direction == "W" || $direction == "E")
        {
            $tempCorrdinate = strval($coordinate);
            $result = substr($tempCorrdinate, 0, 3);
            $minute = substr($tempCorrdinate, 3);
            $result = floatval($result) + floatval($minute / 60.0);
        }
        return $result;
    }
    
    // ------------------------------------华丽的分割线-----------------------------------------------
    
    // 偏移坐标转换为GPS坐标
    private static $TableX;
    private static $TableY;

    static public function getGPSCoordinate($lat, $lng)
    {
        $lat = doubleval($lat);
        $lng = doubleval($lng);
        $result = self::Parse($lng, $lat, $lng, $lat);
        return $result;
    }

    static private function Parse($xMars, $yMars, $xWgs, $yWgs)
    {
        $result = array(
                "Lng" => $xWgs,
                "Lat" => $yWgs 
        );
        if(empty(self::$TableX) || empty(self::$TableY))
        {
            self::LoadText();
        }
        
        $xtry = $xMars;
        $ytry = $yMars;
        
        for($k = 0; $k < 10; ++ $k)
        {
            // 只对中国国境内数据转换
            if($xtry < 72 || $xtry > 137.9 || $ytry < 10 || $ytry > 54.9)
            {
                return $result;
            }
            
            $i = intval(($xtry - 72.0) * 10.0);
            $j = intval(($ytry - 10.0) * 10.0);
            
            $x1 = self::$TableX[self::getId($i, $j)];
            $y1 = self::$TableY[self::getId($i, $j)];
            $x2 = self::$TableX[self::getId($i + 1, $j)];
            $y2 = self::$TableY[self::getId($i + 1, $j)];
            $x3 = self::$TableX[self::getId($i + 1, $j + 1)];
            $y3 = self::$TableY[self::getId($i + 1, $j + 1)];
            $x4 = self::$TableX[self::getId($i, $j + 1)];
            $y4 = self::$TableY[self::getId($i, $j + 1)];
            
            $t = ($xtry - 72.0 - 0.1 * $i) * 10.0;
            $u = ($ytry - 10.0 - 0.1 * $j) * 10.0;
            
            $dx = (1.0 - $t) * (1.0 - $u) * $x1 + $t * (1.0 - $u) * $x2 + $t * $u * $x3 + (1.0 - $t) * $u * $x4 - $xtry;
            $dy = (1.0 - $t) * (1.0 - $u) * $y1 + $t * (1.0 - $u) * $y2 + $t * $u * $y3 + (1.0 - $t) * $u * $y4 - $ytry;
            
            $xtry = ($xtry + $xMars - $dx) / 2.0;
            $ytry = ($ytry + $yMars - $dy) / 2.0;
        }
        
        $result = array(
                "Lng" => $xtry,
                "Lat" => $ytry 
        );
        return $result;
    }

    static private function getId($i, $j)
    {
        return intval($i) + 660 * intval($j);
    }

    static private function LoadText()
    {
        $content = DataFileHandler::getFileContent("Mars2Wgs.txt");
        
        $matchArr = array();
        $rule = '(\d+)';
        preg_match_all($rule, $content, $matchArr);
        
        self::$TableX = array();
        self::$TableY = array();
        $i = intval(0);
        $result = $matchArr[0];
        
        $count = count($result);
        while($i < $count)
        {
            if($i % 2 == 0)
            {
                self::$TableX[$i / 2] = doubleval($result[$i]) / 100000.0;
            }
            else
            {
                self::$TableY[($i - 1) / 2] = doubleval($result[$i]) / 100000.0;
            }
            $i ++;
        }
    }
    
    // ------------------------------------华丽的分割线-----------------------------------------------
    
    // 加偏算法
    private static $pi = 3.14159265358979324;
    //
    // Krasovsky 1940
    //
    // a = 6378245.0, 1/f = 298.3
    // b = a * (1 - f)
    // ee = (a^2 - b^2) / a^2;
    private static $a = 6378245.0;
    private static $ee = 0.00669342162296594323;
    
    //
    // World Geodetic System ==> Mars Geodetic System
    static public function transGPSToMap($wgLat, $wgLon)
    {
        $result = array(
                "Lng" => $wgLon,
                "Lat" => $wgLat 
        );
        
        if(self::outOfChina($wgLat, $wgLon))
        {
            return $result;
        }
        $dLat = self::transformLat($wgLon - 105.0, $wgLat - 35.0);
        $dLon = self::transformLon($wgLon - 105.0, $wgLat - 35.0);
        $radLat = $wgLat / 180.0 * self::pi;
        $magic = Math . Sin($radLat);
        $magic = 1 - self::$ee * $magic * $magic;
        $sqrtMagic = Math . Sqrt($magic);
        $dLat = ($dLat * 180.0) / ((self::$a * (1 - self::$ee)) / ($magic * $sqrtMagic) * self::$pi);
        $dLon = ($dLon * 180.0) / (self::$a / $sqrtMagic * Math . Cos($radLat) * self::$pi);
        $mgLat = $wgLat + $dLat;
        $mgLon = $wgLon + $dLon;
        $result = array(
                "Lng" => $mgLon,
                "Lat" => $mgLat 
        );
        return $result;
    }

    static private function outOfChina($lat, $lon)
    {
        if($lon < 72.004 || $lon > 137.8347)
            return true;
        if($lat < 0.8293 || $lat > 55.8271)
            return true;
        return false;
    }

    static private function transformLat($x, $y)
    {
        $ret = - 100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * Math . Sqrt(Math . Abs($x));
        $ret += (20.0 * Math . Sin(6.0 * $x * self::$pi) + 20.0 * Math . Sin(2.0 * $x * pi)) * 2.0 / 3.0;
        $ret += (20.0 * Math . Sin($y * self::$pi) + 40.0 * Math . Sin($y / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (160.0 * Math . Sin($y / 12.0 * self::$pi) + 320 * Math . Sin($y * self::$pi / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    static private function transformLon($x, $y)
    {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * Math . Sqrt(Math . Abs($x));
        $ret += (20.0 * Math . Sin(6.0 * $x * pi) + 20.0 * Math . Sin(2.0 * $x * self::$pi)) * 2.0 / 3.0;
        $ret += (20.0 * Math . Sin($x * self::$pi) + 40.0 * Math . Sin($x / 3.0 * self::$pi)) * 2.0 / 3.0;
        $ret += (150.0 * Math . Sin($x / 12.0 * self::$pi) + 300.0 * Math . Sin($x / 30.0 * self::$pi)) * 2.0 / 3.0;
        return $ret;
    }
    
    // -----------------------------华丽的分割线----------------------------//
    static public function getDistanceFromGPS($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371393; // 米
        $pk = doubleval(doubleval(180) / 3.14169);
        
        $a1 = doubleval($lat1) / $pk;
        $a2 = doubleval($lng1) / $pk;
        $b1 = doubleval($lat2) / $pk;
        $b2 = doubleval($lng2) / $pk;
        
        $t1 = doubleval(cos($a1) * cos($a2) * cos($b1) * cos($b2));
        $t2 = doubleval(cos($a1) * sin($a2) * cos($b1) * sin($b2));
        $t3 = doubleval(sin($a1) * sin($b1));
        $t4 = floatval(strval($t1 + $t2 + $t3));
        $tt = doubleval(acos($t4));
        
        return $earthRadius * $tt;
    }
}

?>