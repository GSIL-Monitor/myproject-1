<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Common\Utils\IPHandler;
use Common\Utils\VerifyCodeHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class AdminController extends BaseController {

	public function loginAction() {
		return $this->render("CleanAdminBundle:Admin:login.html.twig", array());
	}

	public function verifyCodeImageAction() {
		$vch = new VerifyCodeHandler();
		$code = $vch->getCode();
		$session = new Session();
		$session->start();
		$session->set("verifyCode", $code);
		$vch->doimg();
		exit;
	}

	public function loginSubmitAction() {
		try {
			$loginName = $this->requestParameter("userName", true);
			$password = $this->requestParameter("password");
			$verifyCode = $this->requestParameter("verifyCode", true);
			if (empty($loginName) || empty($password) || empty($verifyCode)) {
				return new Response($this->getAPIResultJson("E020000", "数据填写不完整", ""));
			}
			$session = new Session();
			$session->start();

			$existVerifyCode = $session->get("verifyCode");

			if (empty($existVerifyCode) || strtolower($verifyCode) != strtolower($existVerifyCode)) {
				return new Response($this->getAPIResultJson("E020000", "验证码错误", ""));
			}

			$lmca = $this->get("library_model_clean_adminuser");
			$password = md5(md5($password));
			$adminUser = $lmca->getAdminUserByLogin($loginName, $password);

			if ($adminUser) {
				$session->set("userId", $adminUser->getAdminUserId());
				$session->set("userName", $adminUser->getUserName());
				$session->set("companyId", $adminUser->getCompanyId());

				$adminUser->setLastLoginTime(new \DateTime());
				$adminUser->setLastLoginIp(IPHandler::getClientIP());
				$adminUser->setLoginCount($adminUser->getLoginCount() + 1);
				$lmca->editEntity($adminUser);
				return new Response($this->getAPIResultJson("N00000", "/admin/index", ""));
			}

			return new Response($this->getAPIResultJson("E02000", "登陆失败", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", $ex->getMessage(), ""));
		}
	}

	public function logoutAction() {
		$session = new Session();
		$session->clear();
		header("location:/admin/login");
		exit;
	}

	public function editPassWordAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$adminUserId = $this->LoginUserId;
			$lmcau = $this->get("library_model_clean_adminuser");
			$adminUserEntity = $lmcau->getEntity($adminUserId);
			$oldPassword = $this->requestParameter("oldPassword");
			$newPassword = $this->requestParameter("newPassword");
			if (!$adminUserEntity || !$oldPassword || !$newPassword) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$password = $adminUserEntity->getPassword();
			if ($password != md5(md5($oldPassword))) {
				return new Response($this->getAPIResultJson("E02000", "原密码错误，请重新输入", ""));
			}
			$adminUserEntity->setPassword(md5(md5($newPassword)));
			$lmcau->editEntity($adminUserEntity);
			return new Response($this->getAPIResultJson("N00000", "修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

}
?>