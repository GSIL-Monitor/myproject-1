<?php

namespace Common\Utils\Crypt;

class RSAHandler
{

	//创建公钥和私钥
	static function createKey()
    {
        $dn = array('private_key_bits' => 512,"private_key_type" => OPENSSL_KEYTYPE_RSA);
		//创建公钥和私钥
		$res=openssl_pkey_new($dn); #此处512必须不能包含引号。

		//提取私钥
		openssl_pkey_export($res, $private_key);
		//生成公钥
		$public_key=openssl_pkey_get_details($res);
		$public_key=$public_key["key"];

		$result = array("private_key"=>$private_key,"public_key"=>$public_key);
		return $result;

    }


    //公钥加密，私钥解密
    static function rasDecrypt($str, $private_key)
    {	
    	if(!$private_key)
    	{
//     		$private_key = "-----BEGIN PRIVATE KEY-----
// MIIBVQIBADANBgkqhkiG9w0BAQEFAASCAT8wggE7AgEAAkEA1DpowIGeM4bh90EK
// QM3Adwb6S4omhkLT+U+KY96QQRpCZ1dN7coeKha9OxY2ObHjd8KP8+7eAhVTwzqK
// rmzlGQIDAQABAkEAqScA1Oa6vCD2u8a4MFyN2ZDTMCAlgn+DSkPObrk2ytt6ns6C
// /NfKGXXAOWB0WGlgnVbsLYTXdSnAwbo7mmzzPQIhAOnxtU6lXOonnOdolMIDfiib
// YIPDdo40OhSTuIxILikHAiEA6DyUmBtZU0V/MYmipjixbwe6pOFoeNgvwkIwr4uN
// mN8CIQCzFQwtf/h4Zop9uljli7bvbsGbG+2NPf2X8ty6xiZP0QIgQWlGxSz21OB/
// Odm1aTIQr+AybtxaS6dAlGuGQPuCj2sCIFDzVbAjLOddiLGFMopA+JDTbjdzczSa
// 7G8SKXHpn9W4
// -----END PRIVATE KEY-----";
//     	}
    	$private_key = openssl_get_privatekey($private_key);
        $res = openssl_private_decrypt(base64_decode($str), $decrypted, $private_key);
        if($res)
        {
        	return $decrypted;
        }
        return false;
    }


}

 