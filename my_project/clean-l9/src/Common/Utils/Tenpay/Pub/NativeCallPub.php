<?php

namespace Common\Utils\Tenpay\Pub;

/**
 * 请求商家获取商品信息接口
 */
class NativeCallPub extends WxpayServerPub
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        if($this->returnParameters["return_code"] == "SUCCESS")
        {
            $this->returnParameters["appid"] = $this->wxPayPubConfig["appId"]; // WxPayConf_pub::APPID;//公众账号ID
            $this->returnParameters["mch_id"] = $this->wxPayPubConfig["mchId"]; // WxPayConf_pub::MCHID;//商户号
            $this->returnParameters["nonce_str"] = $this->createNoncestr(); // 随机字符串
            $this->returnParameters["sign"] = $this->getSign($this->returnParameters); // 签名
        }
        return $this->arrayToXml($this->returnParameters);
    }

    /**
     * 获取product_id
     */
    function getProductId()
    {
        $product_id = $this->data["product_id"];
        return $product_id;
    }
}

?>