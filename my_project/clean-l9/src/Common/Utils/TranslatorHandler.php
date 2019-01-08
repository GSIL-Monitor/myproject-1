<?php

namespace Common\Utils;

use Common\Utils\Network\HttpClientHandler;
use Common\Utils\File\DataFileHandler;

class TranslatorHandler
{

    static public function baiduTranslate($from, $to, $content)
    {
        if(empty($content))
        {
            return $content;
        }
        $host = "openapi.baidu.com";
        $url = "http://openapi.baidu.com/public/2.0/bmt/translate";
        $data = array(
                "client_id" => "TuBSvCzzGtth4fhOPGFpGuiT",
                "q" => $content,
                "from" => $from,
                "to" => $to 
        );
        $httpClient = new HttpClientHandler($host);
        $response = $httpClient->quickPost($url, $data);
        $resultArr = json_decode($response, true, 10);
        if(is_array($resultArr) && array_key_exists("trans_result", $resultArr))
        {
            
            return $resultArr["trans_result"][0]["dst"];
        }
        return $content;
    }

    static public function microsoftTranslate($from, $to, $content)
    {
        if(empty($content))
        {
            return $content;
        }
        $host = "api.microsofttranslator.com";
        $url = "http://api.microsofttranslator.com/v2/ajax.svc/TranslateArray";
        $content = "[\"" . $content . "\"]";
        $url = $url . "?appId=TFp7eGrDQM0WZowqP0jh2eecFqZCClehLs9ho6x6VkZs*&from=" . $from . "&to=" . $to . "&texts=" . urlencode($content);
        $httpClient = new HttpClientHandler($host);
        $response = $httpClient->quickGet($url);
        $response = UtilityHandler::prepareJSON($response);
        $resultArr = json_decode($response, true); // json_decode($response,true,10);
        if(is_array($resultArr) & is_array($resultArr[0]) && array_key_exists("TranslatedText", $resultArr[0]))
        {
            return $resultArr[0]["TranslatedText"];
        }
        return $content;
    }
    
    // 该方法会有性能问题
    static public function translateChineseToOther($to, $content)
    {
        // return $content;exit;
        if(empty($content))
        {
            return $content;
        }
        // LogHandler::writeLog(date("Y-m-d H:i:s").">>1\r\n".$content."\r\n","tran");
        $matches = array();
        preg_match_all('/[\x{4e00}-\x{9fa5}]+/u', $content, $matches);
        $matches = array_unique($matches[0]);
        $contentTemp = implode("|", $matches);
        LogHandler::writeLog(date("Y-m-d H:i:s") . ">>2\r\n" . $contentTemp . "\r\n", "tran");
        // $contentTemp="这身上都要碎了|月|哈哈|一个多月了|现在才贴防撞条|霸气啊|另外一台放着对比|觉悟丶佳思|何明|连续签到怎么成一个乐币了|没少签到啊|天使落泪心|人如|满电时精神抖擞|没电时就忧心怠倦|又是周一|您满电归来了吗|乐行小秘书|你狠丶我比你更狠|好几天没练单腿啦|有点生疏啦|快乐出行|早安|义乌|专用优质播放器|话说|大家都用什么播放器|我现在用这个天天动听更新后放的音乐很烂了|大家有好介绍吗|会跑的瓜子|成功进入乐行周排行榜|金币积分都已到手|感觉自己萌萌的|各位不服来战|不知道怎么的得进入前|啦|暂时的第一名|今天好热|开不开机了怎么办|杨东坡|只有周一能这排名啦|迎着早晨的阳光|和老头老太们的目光|俱乐部丹寨龙泉山游记|南极客平衡车俱乐部|大家早晨|陈卫英|早上好|新一周的工作开始了|暮暮|小清童鞋|签到|请问小三是用没电再冲呢还是随用随充呢|小蜗牛|一教就会|贵阳鸿通城乐行车行开业了|贵阳的朋友还等什么|赶紧行动吧|把你心爱的|小三|骑回家|青少年|又出去游玩了一番|乐行车果然拉风炫酷|有没有车友下次要一起出门的|棒棒糖|骑着|搞宵夜|醉驾了|陸小陸|两个星期基本没骑了|五一前这几天跑跑吧|无齿的情兽";
        $contentTemp = self::microsoftTranslate("zh", $to, $contentTemp);
        LogHandler::writeLog(date("Y-m-d H:i:s") . "\r\n" . $contentTemp . "\r\n", "tran");
        $tempArr = split("\\|", $contentTemp);
        for($i = 0; $i < count($matches); $i ++)
        {
            $tempStr = trim($tempArr[$i]);
            if(empty($tempStr) || empty($matches[$i]))
            {
                continue;
            }
            $content = str_replace($matches[$i], $tempStr, $content);
        }
        return $content;
    }
}

?>