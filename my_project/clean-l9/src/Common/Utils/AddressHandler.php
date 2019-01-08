<?php

namespace Common\Utils;

use Common\Utils\File\DataFileHandler;

class AddressHandler
{

    static function getFullAddressStrFromCode($province_code, $city_code, $area_code)
    {
        $addStr = '';
        $arr = DataFileHandler::getFileToArray("area.json");
        // $arr = json_decode(file_get_contents("./data/area.json"),true);
        foreach($arr['provinces'] as $province)
        {
            if($province['code'] == $province_code)
            {
                $addStr .= $province['name'];
                if(isset($province['citys']))
                {
                    foreach($province['citys'] as $city)
                    {
                        if($city['code'] == $city_code)
                        {
                            $addStr .= $city['name'];
                            if(isset($city['areas']))
                            {
                                foreach($city['areas'] as $area)
                                {
                                    if($area['code'] == $area_code)
                                    {
                                        $addStr .= $area['name'];
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                break;
            }
        }
        return $addStr;
    }

    static function getProvinceName($province_code)
    {
        $addStr = '';
        $arr = DataFileHandler::getFileToArray("area.json");
        foreach($arr['provinces'] as $province)
        {
            if($province['code'] == $province_code)
            {
                $addStr .= $province['name'];
                break;
            }
        }
        return $addStr;
    }

    static function getCityName($province_code, $city_code)
    {
        $addStr = '';
        $arr = DataFileHandler::getFileToArray("area.json");
        foreach($arr['provinces'] as $province)
        {
            if($province['code'] == $province_code)
            {
                // $addStr.=$province['name'];
                if(isset($province['citys']))
                {
                    foreach($province['citys'] as $city)
                    {
                        if($city['code'] == $city_code)
                        {
                            $addStr .= $city['name'];
                            break;
                        }
                    }
                }
            }
        }
        return $addStr;
    }

    static function getProvinceCityName($province_code, $city_code)
    {
        $addStr = '';
        $arr = DataFileHandler::getFileToArray("area.json");
        foreach($arr['provinces'] as $province)
        {
            if($province['code'] == $province_code)
            {
                $addStr .= $province['name'];
                if(isset($province['citys']))
                {
                    foreach($province['citys'] as $city)
                    {
                        if($city['code'] == $city_code)
                        {
                            $addStr .= $city['name'];
                            break;
                        }
                    }
                }
            }
        }
        return $addStr;
    }
}

?>