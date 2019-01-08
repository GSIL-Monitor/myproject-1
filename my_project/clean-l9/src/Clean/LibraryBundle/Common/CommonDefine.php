<?php

namespace Clean\LibraryBundle\Common;

class CommonDefine
{
    // 逻辑删除状态
    const DATA_STATUS_DELETE = 0;
    const DATA_STATUS_NORMAL = 1;

    
    // 对称加密
    const AES_KEY = "EJKD93LJK8923ACD";
    const AES_IV = "23KCSLED43DFJKDE";
    
    // 字符串拼接字符
    const SPLIT_WORD = "@";
    
    //设备类型
    const DEVICE_TYPE_IOS = 1;
    const DEVICE_TYPE_ANDROID = 2;
    const DEVICE_TYPE_WEB = 3;

    
    //后台基本信息关键词
    const BASIC_INFO_KEY_ProductIntroduction = 'productIntroduction';

    
    const IS_FILTER = 1; // 是否过滤
    
    //开放关闭状态
    const OPEN_STATUS_FALSE = 0;
    const OPEN_STATUS_TRUE = 1;
    
  
    //语言
    const CHINESE = 'cn';
    const ENGLISH = 'en';
    const HONG_KONG = 'hk';
    const TAI_WAN = 'tw';
    const KOREA = 'ko';

}
?>