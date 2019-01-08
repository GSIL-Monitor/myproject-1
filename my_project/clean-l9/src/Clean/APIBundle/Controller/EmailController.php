<?php
/**
 * Created by PhpStorm.
 * User: AZ
 * Date: 2019/1/3
 * Time: 10:58
 */

namespace Clean\APIBundle\Controller;


use Symfony\Component\HttpFoundation\Response;

class EmailController extends BaseController
{
    private $user = [
        'xp@ldrobot.com',
        'wf@ldrobot.com',
    ];

    private $sender = 'xiaogouzhineng@moomv.com';
    private $pwd = 'gL6jx25yrusaqTLb';
    private $host = 'smtp.qiye.163.com';
    private $port = '994';

    public function rootDiskAction()
    {
        $param = $this->requestParameter('disk');
        $subject = 'Web 服务器, 根目录‘/’,硬盘使用率预警';
        $msg = 'web服务器，根目录‘/’,磁盘当前利用率的百分值为 '.$param.' %，超过预警阀值，请知悉！！！！！！';
        foreach ($this->user as $item) {
            $this->sendEmail($item, $msg, $subject);
        }
        return new Response('success');
    }

    public function homeDiskAction()
    {
        $param = $this->requestParameter('disk');
        $subject = 'Web 服务器, 项目目录‘/mnt’,硬盘使用率预警';
        $msg = 'web服务器，项目目录‘/mnt’,磁盘当前利用率的百分值为 '.$param.' %，超过预警阀值，请知悉！！！！！！';
        foreach ($this->user as $item) {
            $this->sendEmail($item, $msg, $subject);
        }
        return new Response('success');
    }

    public function appMonitorAction()
    {
        $url = 'https://www.xiaogou111.com/api/getUserInfo?onlog=4';
        $result = file_get_contents($url);
        if ($result && $result_arr = $this->isJson($result, true)){
            if ($result_arr['code'] == 'N00000') return new Response('success');
        }
        $subject = 'APP监控系统邮件';
        $msg = '监控程序发现APP接口无法访问，请检查！！！！！！';
        foreach ($this->user as $item) {
            $this->sendEmail($item, $msg, $subject);
        }
        return new Response('success');
    }

    private function sendEmail($user, $msg, $subject = 'Web 服务器, 硬盘使用率预警')
    {

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get("translator")->trans($subject))
            ->setFrom($this->sender, $this->get("translator")
                ->trans('监控预警邮件'))
            ->setTo($user)
            ->setBody($this->get("translator")->trans('警告') . " ：" . $msg )
            ->setEncoder(\Swift_Encoding::getBase64Encoding());

        $transport = \Swift_SmtpTransport::newInstance($this->host, $this->port, "ssl")
            ->setUsername($this->sender)
            ->setPassword($this->pwd);
        // 创建mailer对象
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($message);

        return;
    }


    /**
     * 判断字符串是否为 Json 格式
     *
     * @param  string $data Json 字符串
     * @param  bool $assoc 是否返回关联数组。默认返回对象
     *
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    private function isJson($data = '', $assoc = false)
    {
        $data = json_decode($data, $assoc);
        if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
            return $data;
        }
        return false;
    }
}