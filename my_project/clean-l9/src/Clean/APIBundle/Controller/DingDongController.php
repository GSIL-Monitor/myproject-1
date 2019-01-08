<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2018/12/19
 * Time: 11:47
 */

namespace Clean\APIBundle\Controller;

use Common\Utils\ConfigHandler;
use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DingDongController extends BaseController
{
    private $application_id = '';
    private $dingdong_url = 'linglong://open?openAuthResult=1';
    private $dingdong_aes_key = '95e2a661e154a6832b22541eaf2e64ae95e2a661e154a6832b22541eaf2e64ae95e2a661e154a6832b22541eaf2e64ae95e2a661e154a6832b22541eaf2e64ae';
    private $authLogon = 'https://www.xiaogou111.com/logon.html';

    /**
     * 用户登陆认证
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function loginAuthAction()
    {
        $username = $this->requestParameter('loginName');
        $pwd = $this->requestParameter('password');
        $state = $this->requestParameter('state');
        LogHandler::writeLog($username.'\n', 'dingdong/login');
        LogHandler::writeLog($pwd.'\n', 'dingdong/login');
        LogHandler::writeLog($state, 'dingdong/login');
        if (!$username || !$pwd || !$state) {
            return new Response($this->getAPIResultJson('E02000', '缺少重要参数', ''));
        }

        $m_userinfo = $this->get('library_model_clean_userinfo');
        $pwd = md5(md5($pwd));

        $login_result = $m_userinfo->getUserInfoByLogin($username, $pwd);
        if (!$login_result) {
            return new Response($this->getAPIResultJson('E02000', '用户信息错误', ''));
        }

        //AES加密解密验证
        //$aesencrypt = openssl_encrypt($state, 'AES-128-ECB', ConfigHandler::getCommonConfig('DingDongAesKey'));
        //$aesencrypt = urlencode(base64_encode($aesencrypt));

        //$state_json = openssl_decrypt(base64_decode(urldecode($state)), 'AES-128-ECB', ConfigHandler::getCommonConfig('DingDongAesKey'));
        $state_json = openssl_decrypt($state, 'AES-128-ECB', md5($this->dingdong_aes_key, true));
        $state_data = $this->isJson($state_json, true);
        if (!$state_data || !$state_data['userid']) {
            return new Response($this->getAPIResultJson('E02000', '权限验证失败', ''));
        }
        LogHandler::writeLog($state_json, 'dingdong/login');
        //缓存  ************ session
        $session = new Session();
        $session->set('application_id', $state_data['appid']);
        $this->application_id = $state_data['appid'];
        $login_result->setDingDongUserId($state_data['userid']);
        $m_userinfo->editEntity($login_result);
        LogHandler::writeLog('the end', 'dingdong/login');
        return new Response($this->getAPIResultJson('N00000', '授权成功', $this->dingdong_url));
    }

    public function cloudInterAction()
    {
        $json = file_get_contents('php://input');
        $timestamp = $this->requestParameter('timestamp');
        $sign = $this->requestParameter('sign');
        LogHandler::writeLog($json, 'dingdong/cloudInter');
        LogHandler::writeLog($timestamp, 'dingdong/cloudInter');
        LogHandler::writeLog($sign, 'dingdong/cloudInter');
        if (!$json || !$timestamp || !$sign || !$dingdong_data = $this->isJson($json, true)) {
            return new Response($this->getAPIResultJson('E02000', '缺少重要参数', ''));
        }

        $session = new Session();
        //if (!$this->application_id && $session->get('application_id')) return $this->redirect(ConfigHandler::getCommonConfig('authLoginUrl'));
        if (!$this->application_id && $session->get('application_id')) return new Response($this->getAPIResultJson('E02000', '请先授权', ''));
        $application_id = $this->application_id ?? $session->get('application_id');
        $my_sign = md5($application_id . $json . $timestamp);
        LogHandler::writeLog($my_sign, 'dingdong/cloudInter');
        if ($sign != $my_sign) {
            return new Response($this->getAPIResultJson('E02000', '权限验证失败', $my_sign));
        }
        $m_userinfo = $this->get('library_model_clean_userinfo');
        $userinfo_result = $m_userinfo->getUserInfoByDingDongUserId($dingdong_data['user']['user_id']);
        //if (!$userinfo_result) return $this->redirect(ConfigHandler::getCommonConfig('authLoginUrl'));
        if (!$userinfo_result) return new Response($this->getAPIResultJson('E02000', '请先授权', ''));
        $userid = $userinfo_result->getUserId();
        $sn = $userinfo_result->getNowSn();
        $user_data = [
            'userid' => $userid,
            'sn' => $sn
        ];
        LogHandler::writeLog(json_encode($user_data), 'dingdong/cloudInter');
        switch ($dingdong_data['status']) {
            case 'LAUNCH':
                $result = $this->doLaunch($dingdong_data);
                break;
            case 'INTENT':
                $result = $this->doIntent($dingdong_data, $user_data);
                break;
            case 'NOTICE':
                $result = $this->doNotice($dingdong_data);
                break;
            case 'END':
                $result = $this->doEnd($dingdong_data);
                break;
        }
        LogHandler::writeLog(json_encode($result), 'dingdong/cloudInter');
        return new Response(json_encode($result));
    }

    public function doLaunch($data)
    {
        //session 判断
        $result = [
            'versionid' => $data['versionid'],
            'is_end' => false,
            'sequence' => $data['sequence'],
            'timestamp' => $this->msectime(),
            'directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '主人需要小狗提供什么服务'
                ]
            ],
            /*'push_to_app' => [
                'title' => '小狗智能竭诚为您服务',
                'type' => 1,
                'text' => '感谢您使用小狗智能，我们竭诚为您服务，欢迎下次使用！'
            ],
            'repeat_directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '感谢您使用小狗智能'
                ]
            ],*/
            'extend' => [
                'NO_REC' => 0
            ]
        ];
        return $result;
    }

    public function doIntent($data, $user_data)
    {
        $result = [
            'versionid' => $data['versionid'],
            'is_end' => true,
            'sequence' => $data['sequence'],
            'timestamp' => $this->msectime(),
            'directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '好的，主人'
                ]
            ],
            /*'push_to_app' => [
                'title' => '小狗智能竭诚为您服务',
                'type' => 1,
                'text' => '感谢您使用小狗智能，我们竭诚为您服务，欢迎下次使用！'
            ],
            'repeat_directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '感谢您使用小狗智能'
                ]
            ],*/
            'extend' => [
                'NO_REC' => 0
            ]
        ];
        $res = [
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
        switch ($data['slots']['type']) {
            case 'clean':
            case 'clean_open':
                $res["infoType"] = 21005;
                $res['data']['mode'] = 'smartClean';
                break;
            case 'clean_continue':
                $res["infoType"] = 21017;
                $res['data']['cmd'] = 'continue';
                break;
            case 'clean_close':
            case 'clean_pause':
                $res['data']['cmd'] = 'pause';
                $res["infoType"] = 21017;
                break;
        }
        $res = json_encode($res, JSON_UNESCAPED_SLASHES) . "#\t#";
        $res = str_replace(" ", "+", $res);
        AlexaHandler::swooleClient($res, 9501);
        return $result;
    }

    public function doNotice($data)
    {
        $result = [
            'versionid' => $data['versionid'],
            'is_end' => false,
            'sequence' => $data['sequence'],
            'timestamp' => $this->msectime(),
            'directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '主人需要小狗提供什么服务'
                ]
            ],
            /*'push_to_app' => [
                'title' => '小狗智能竭诚为您服务',
                'type' => 1,
                'text' => '感谢您使用小狗智能，我们竭诚为您服务，欢迎下次使用！'
            ],
            'repeat_directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '感谢您使用小狗智能'
                ]
            ],*/
            'extend' => [
                'NO_REC' => 1
            ]
        ];
        return $result;
    }

    public function doEnd($data)
    {
        $result = [
            'versionid' => $data['versionid'],
            'is_end' => true,
            'sequence' => $data['sequence'],
            'timestamp' => $this->msectime(),
            'directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '感谢您使用小狗智能'
                ]
            ],
            'push_to_app' => [
                'title' => '小狗智能竭诚为您服务',
                'type' => 1,
                'text' => '感谢您使用小狗智能，我们竭诚为您服务，欢迎下次使用！'
            ],
            /*'repeat_directive' => [
                'directive_items' => [
                    'type' => 1,
                    'content' => '感谢您使用小狗智能'
                ]
            ],*/
            'extend' => [
                'NO_REC' => 0
            ]
        ];
        return $result;
    }

    //返回当前的毫秒时间戳
    public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
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