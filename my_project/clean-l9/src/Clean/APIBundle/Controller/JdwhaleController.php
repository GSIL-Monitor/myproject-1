<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2018/12/24
 * Time: 18:29
 */

namespace Clean\APIBundle\Controller;


use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;

class JdwhaleController extends BaseController
{

    private $jd_key = '486538D4D9894671';
    private $client_id = 'e884693a2c15b02ea9efa3f752b10fcc';
    private $client_secret = '9dfb844b7bf46ab625dbc6765ee9d8c4';

    public function loginAuthAction()
    {
        $username = $this->requestParameter('loginName');
        $pwd = $this->requestParameter('password');
        $client_id = $this->requestParameter('client_id');
        //$response_type = $this->requestParameter('response_type');
        $redirect_uri = $this->requestParameter('redirect_uri');
        $state = $this->requestParameter('state');
        if (!$username || !$pwd || !$redirect_uri || !$state || strlen($pwd) != 32 || !$client_id) {
            return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
        }
        LogHandler::writeLog($client_id, 'Jdwhale/login');
        if ($client_id != $this->client_id) {
            return new Response($this->getAPIResultJson("E02000", "认证失败", ""));
        }
        $m_userinfo = $this->get('library_model_clean_userinfo');
        if ($this->isPhone($username)) {
            $username = "+86&nbsp;" . $username;
        }
        $password = md5(md5($pwd));
        $userinfo = $m_userinfo->getUserInfoByLogin($username, $password);
        LogHandler::writeLog(json_encode($userinfo), 'Jdwhale/login');
        if (!$userinfo) {
            return new Response($this->getAPIResultJson("E02000", "用户信息错误", ""));
        }
        $token = $this->setToken();
        LogHandler::writeLog($token, 'Jdwhale/login');
        $userinfo->setDingDongUserId($token);
        $m_userinfo->editEntity($userinfo);
        $url = urldecode($redirect_uri) . '?state=' . $state . "&code=" . $token;
        LogHandler::writeLog($url, 'Jdwhale/login');
        return new Response($this->getAPIResultJson("N00000", "获取code成功", $url));
    }

    public function authTokenAction()
    {
        $grant_type = $this->requestParameter('grant_type');
        $client_id = $this->requestParameter('client_id');
        $client_secret = $this->requestParameter('client_secret');
        $redirect_uri = $this->requestParameter('redirect_uri');
        $code = $this->requestParameter('code');
        LogHandler::writeLog($redirect_uri, 'Jdwhale/token');
        if (!$grant_type || !$client_id || !$client_secret || !$redirect_uri || !$code) {
            return new Response($this->getJson("E02000", "数据填写不完整", ""));
        }
        if ($client_id != $this->client_id || $client_secret != $this->client_secret) {
            return new Response($this->getJson("E02000", "认证失败", ""));
        }
        LogHandler::writeLog($code, 'Jdwhale/token');
        $m_userinfo = $this->get('library_model_clean_userinfo');
        $userinfo = $m_userinfo->getUserInfoByDingDongUserId($code);
        if (!$userinfo) {
            return new Response($this->getJson("E02000", "用户信息错误", ""));
        }
        $token = $this->setToken();
        LogHandler::writeLog($token, 'Jdwhale/token');
        $userinfo->setDingDongUserId($token);
        $m_userinfo->editEntity($userinfo);

        $data = array(
            "access_token" => $token,
            "refresh_token" => $token,
            "expires_in" => 17600000,
        );
        LogHandler::writeLog($data, 'Jdwhale/token');
        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function callSkillAction()
    {
        $postData = file_get_contents('php://input');
        LogHandler::writeLog($postData, "Jdwhale/callSkill");

        if (!$postData || !$temRes = $this->isJson($postData, true)) {
            return new Response("Wrong request");
        }

        $header = $temRes["header"];
        $payload = $temRes["payload"];
        $accessToken = $payload["accessToken"];
        /*if (!$accessToken) {
            return new Response("Please authenticate user information first");
        }*/
        //获取名字
        $intentName = $header["name"];
        //获取userId
        $lmcu = $this->get("library_model_clean_userinfo");
        $userInfo = $lmcu->getEntityByAuthenticationToken($accessToken);
        if (!$userInfo) {
            return new Response("error authenticate user information");
        }
        $userId = $userInfo->getUserId();
        $nowSn = $userInfo->getNowSn();
        if ($nowSn && strlen($nowSn) == 16) {
            //获取机器消息
            $lmcm = $this->get("library_model_clean_machine");
            $machineInfo = $lmcm->getMachineBySn($nowSn);
        }

        switch ($intentName) {
            case "DiscoveryDevicesRequest":
                $deviceId = $machineInfo->getMachineId();
                $machineName = $machineInfo->getMachineName() ? $machineInfo->getMachineName() : $nowSn;

                //需要判断扫地机是否在线###############
                $properties = 1;

                $res = $this->doDiscoveryDevices($postData, $deviceId, $machineName, $properties);
                break;
            case "TurnOnRequest":
                $res = $this->doTurnOn($postData, $userId, $nowSn);
                break;
            case "PlayRequest":
                $res = $this->doPlay($header, $userId, $nowSn);
                break;
            case "PauseRequest":
                $res = $this->doPause($header, $userId, $nowSn);
                break;
            case "StopRequest":
                $res = $this->doStop($header, $userId, $nowSn);
                break;
            case "TurnOffRequest":
                $res = $this->doTurnOff($header, $userId, $nowSn);
                break;
            default:
                $res = array(
                    "infoType" => 21017,
                    "connectionType" => 2,
                    "deviceType" => 3,
                );
                $data = array(
                    "userId" => $userId,
                    "cmd" => "continue",
                );

                $res["data"] = CheckCryptHandler::encrypt(json_encode($data));
                $data = json_encode($res) . "#\t#";
                break;

        }

        return new Response($this->getJson("N00000", "成功", $res));
    }

    private function doDiscoveryDevices($postData, $deviceId, $machineName, $properties)
    {
        $header = array(
            "namespace" => $postData['header']["namespace"],
            "name" => "DiscoveryDevicesResponse",
            "messageId" => $postData['header']["messageId"],
            "payLoadVersion" => $postData['header']["payLoadVersion"],
        );
        $devices[] = array(
            "deviceId" => $deviceId,
            "friendlyName" => "小狗智能",
            "modelName" => $machineName,
            "deviceTypes" => "SWEEPING_ROBOT",
            "isReachable" => $properties,
            "actions" => ["TurnOn", "TurnOff", "Pause", "Stop", "Play"],
            "controlSpeech" => ['开启', '关闭', '暂停', '停止', '继续'],
        );

        $res = array(
            "header" => $header,
            "payload" => array("deviceInfo" => $devices),
        );
        return $res;
    }

    private function doTurnOn($postData, $userId, $nowSn)
    {
        $res = array(
            "infoType" => 21005,
            "connectionType" => 1,
            "deviceType" => 4,
        );
        $res["dInfo"] = array(
            "ts" => time(),
            "userId" => $userId,
        );
        $res["data"] = array(
            "sn" => $nowSn,
            "mode" => "smartClean",
        );
        $this->requestSwoole($res);

        $header = array(
            "namespace" => $postData['header']["namespace"],
            "name" => "TurnOnResponse",
            "messageId" => $postData['header']["messageId"],
            "payLoadVersion" => $postData['header']["payLoadVersion"],
        );

        $payload = array("result" => 'SUCCESS');

        $res = array(
            "header" => $header,
            "payload" => $payload,
        );
        return $res;
    }

    private function doPlay($data, $userId, $nowSn)
    {
        $res = array(
            "infoType" => 21017,
            "connectionType" => 1,
            "deviceType" => 4,
        );
        $res["dInfo"] = array(
            "ts" => time(),
            "userId" => $userId,
        );
        $res["data"] = array(
            "sn" => $nowSn,
            "cmd" => "continue",
        );

        $this->requestSwoole($res);

        $header = array(
            "namespace" => $data["namespace"],
            "name" => "PlayResponse",
            "messageId" => $data["messageId"],
            "payLoadVersion" => $data["payLoadVersion"],
        );

        $payload = array("result" => 'SUCCESS');

        $res = array(
            "header" => $header,
            "payload" => $payload,
        );
        return $res;
    }

    private function doPause($data, $userId, $nowSn)
    {
        $res = array(
            "infoType" => 21017,
            "connectionType" => 1,
            "deviceType" => 4,
        );
        $res["dInfo"] = array(
            "ts" => time(),
            "userId" => $userId,
        );
        $res["data"] = array(
            "sn" => $nowSn,
            "cmd" => "pause",
        );

        $this->requestSwoole($res);
        $header = array(
            "namespace" => $data["namespace"],
            "name" => "PauseResponse",
            "messageId" => $data["messageId"],
            "payLoadVersion" => $data["payLoadVersion"],
        );

        $payload = array("result" => 'SUCCESS');

        $res = array(
            "header" => $header,
            "payload" => $payload,
        );
        return $res;
    }

    private function doStop($data, $userId, $nowSn)
    {
        $res = array(
            "infoType" => 21017,
            "connectionType" => 1,
            "deviceType" => 4,
        );
        $res["dInfo"] = array(
            "ts" => time(),
            "userId" => $userId,
        );
        $res["data"] = array(
            "sn" => $nowSn,
            "cmd" => "pause",
        );

        $this->requestSwoole($res);
        $header = array(
            "namespace" => $data["namespace"],
            "name" => "StopResponse",
            "messageId" => $data["messageId"],
            "payLoadVersion" => $data["payLoadVersion"],
        );

        $payload = array("result" => 'SUCCESS');

        $res = array(
            "header" => $header,
            "payload" => $payload,
        );
        return $res;
    }

    private function doTurnOff($data, $userId, $nowSn)
    {
        $res = array(
            "infoType" => 21017,
            "connectionType" => 1,
            "deviceType" => 4,
        );
        $res["dInfo"] = array(
            "ts" => time(),
            "userId" => $userId,
        );
        $res["data"] = array(
            "sn" => $nowSn,
            "cmd" => "pause",
        );

        $this->requestSwoole($res);
        $header = array(
            "namespace" => $data["namespace"],
            "name" => "TurnOffResponse",
            "messageId" => $data["messageId"],
            "payLoadVersion" => $data["payLoadVersion"],
        );

        $payload = array("result" => 'SUCCESS');

        $res = array(
            "header" => $header,
            "payload" => $payload,
        );
        return $res;
    }

    public function interAction()
    {
        $json = file_get_contents('php://input');
        $request_date = $this->requestParameter('Request-Date');
        $skill_tokene = $this->requestParameter('Skill-Token');
        if (!$json || !$request_date || !$skill_tokene || !$data = $this->isJson($json, true)) {
            return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
        }
        $my_sign = $this->oauth($json, $request_date);
        if ($skill_tokene != $my_sign) {
            return new Response($this->getAPIResultJson("E02000", "认证失败", ""));
        }
        //获取userId
        $m_userinfo = $this->get("library_model_clean_userinfo");
        $userinfo = $m_userinfo->getUserInfoByDingDongUserId($data['session']['user']['accessToken']);
        $userid = $userinfo->getUserId();
        $sn = $userinfo->getNowSn();
        /*if ($sn && strlen($sn) == 16) {
            //获取机器消息
            $lmcm = $this->get("library_model_clean_machine");
            $machineInfo = $lmcm->getMachineBySn($sn);
        }*/
        $user_data = [
            'userid' => $userid,
            'sn' => $sn
        ];
        switch ($data['request']['type']) {
            case 'LaunchRequest':
                $result = $this->doLaunch($data, $user_data);
                break;
            case 'IntentRequest':
                $result = $this->doIntent($data, $user_data);
                break;
            case 'SessionEndedRequest':
                $result = $this->doSessionEnded($data, $user_data);
                break;

        }
        return new Response(json_encode($result));
    }

    private function doLaunch($data, $user_data)
    {
        $result = [
            'version' => $data['version'],
            'contexts' => [],
            'response' => [
                'output' => [
                    'type' => 'PlainText',
                    'text' => '好的，小狗正在努力清扫'
                ]
            ],
            'directives' => [],
            'shouldEndSession' => true
        ];
        $swoole_data = [
            "infoType" => 21005,
            "connectionType" => 1,
            "deviceType" => 4,
            'dInfo' => [
                "ts" => time(),
                "userId" => $user_data['userid'],
            ],
            'data' => [
                "sn" => $user_data['sn'],
                "mode" => "smartClean",
            ]
        ];
        $this->requestSwoole($swoole_data);
        return $result;
    }

    private function doIntent($data, $user_data)
    {
        $result = [
            'version' => $data['version'],
            'contexts' => [],
            'response' => [
                'output' => [
                    'type' => 'PlainText',
                    //'text' => '好的，小狗继续努力清扫中'
                ]
            ],
            'directives' => [],
            'shouldEndSession' => true
        ];
        $swoole_data = [
            "connectionType" => 1,
            "deviceType" => 4,
            "dInfo" => [
                "ts" => time(),
                "userId" => $user_data['userid']
            ],
            "data" => [
                "sn" => $user_data['sn']
            ]
        ];
        switch ($data['request']['intent']['name']) {
            case 'HelpIntent':
            case 'OpenIntent':
                $swoole_data["infoType"] = 21005;
                $swoole_data['data']['mode'] = 'smartClean';
                $result['response']['output']['text'] = '好的，小狗正在努力清扫';
                break;
            case 'ResumeIntent':
                $swoole_data["infoType"] = 21017;
                $swoole_data['data']['cmd'] = 'continue';
                $result['response']['output']['text'] = '好的，小狗继续努力清扫中';
                break;
            case 'PauseIntent':
            case 'CancelIntent':
                $swoole_data['data']['cmd'] = 'pause';
                $swoole_data["infoType"] = 21017;
                $result['response']['output']['text'] = '好的，小狗已进入休息模式';
                break;
        }
        $this->requestSwoole($swoole_data);
        return $result;
    }

    private function doSessionEnded($data, $user_data)
    {
        $result = [
            'version' => $data['version'],
            'contexts' => [],
            'response' => [
                'output' => [
                    'type' => 'PlainText',
                    'text' => '好的，小狗已进入休息模式'
                ]
            ],
            'directives' => [],
            'shouldEndSession' => true
        ];
        $swoole_data = [
            "infoType" => 21017,
            "connectionType" => 1,
            "deviceType" => 4,
            'dInfo' => [
                "ts" => time(),
                "userId" => $user_data['userid'],
            ],
            'data' => [
                "sn" => $user_data['sn'],
                "cmd" => "pause",
            ]
        ];
        $this->requestSwoole($swoole_data);
        return $result;
    }

    private function requestSwoole($data)
    {
        $res = json_encode($data, JSON_UNESCAPED_SLASHES) . "#\t#";
        $res = str_replace(" ", "+", $res);
        $res = AlexaHandler::swooleClient($res, 9501);
    }

    /**
     * 响应消息头为application/json;charset=UTF-8
     * @param $code
     * @param $message
     * @param $data
     * @return false|string
     */
    private function getJson($code, $message, $data) {
        if ($code == "N00000") {
            $result = json_encode($data);
        } else {
            $res = array("error" => $code, "error_description" => $message);
            $result = json_encode($res);
        }
        return $result;
    }

    /**
     * 生成唯一token
     * @return string
     */
    private function setToken()
    {
        $str = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
        $str = md5(sha1($str));  //加密
        return $str;
    }

    /**
     * 签名验证
     * @param $body
     * @param $date
     * @return string
     */
    private function oauth($body, $date)
    {
        //Token = MD5(RequestBody的JSON内容 + "#" + 密钥 + "@" + Request-Date)
        return md5($body.'#'.$this->jd_key.'@'.$date);
    }

    /**
     * 验证手机号
     * @param $phone
     * @return bool
     */
    private function isPhone($phone)
    {
        $regex = "^((13[0-9])|(14[5,7,9])|(15([0-3]|[5-9]))|(166)|(17[0,1,3,5,6,7,8])|(18[0-9])|(19[8|9]))\\d{8}$^";
        if(preg_match($regex, $phone)){
            return true;
        }
        return false;
    }

    /**
     * 判断字符串是否为 Json 格式
     *
     * @param  string $data Json 字符串
     * @param  bool $assoc 是否返回关联数组。默认返回对象
     *
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    public function isJson($data = '', $assoc = false)
    {
        $data = json_decode($data, $assoc);
        if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
            return $data;
        }
        return false;
    }
}