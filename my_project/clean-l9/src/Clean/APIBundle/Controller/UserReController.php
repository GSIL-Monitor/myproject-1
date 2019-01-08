<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\UserInfoEntity;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\AESCryptHandler;
use Common\Utils\File\FileCommonHandler;
use Common\Utils\IPHandler;
use Common\Utils\LogHandler;
use Common\Utils\PhoneMessageHandler;
use Symfony\Component\HttpFoundation\Response;

class UserReController extends BaseController {

	//同步APP版本号
	public function synchronizeAppVersionAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$appVersion = $this->getParameter("appVersion"); // 手机接受验证码
			if (!$appVersion) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ''));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getEntity($userId);
			$userInfo->setUserAppVersion($appVersion);
			$lmcu->editEntity($userInfo);

			return new Response($this->getAPIResultJson("N00000", "同步成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//用户重置扫地机配网信息
	public function resetMachineNetStatusAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getEntity($userId);
			$userInfo->setIsStartBind(0);
			$lmcu->editEntity($userInfo);

			return new Response($this->getAPIResultJson("N00000", "重置成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//注销
	public function cancelUserInfoAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$lmcu->deleteEntity($userId);

			return new Response($this->getAPIResultJson("N00000", "注销成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	//发送手机验证码
	public function verifyPhoneCodeAction() {
		$phone = $this->getParameter("phone"); // 手机接受验证码
		if (!$phone || !is_numeric($phone)) {
			return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
		}

		$areaCode = $this->getParameter("areaCode");
		if (!$areaCode || !is_numeric($areaCode)) {
			return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
		}

		$type = intval($this->getParameter("type"));
		if (!$type) {
			return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
		}

		if ($areaCode == 86) {
			if (strlen($phone) != 11) {
				return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
			}
			$pushPhone = $phone;
		} else {
			//接收号码格式为00+国际区号+号码，如“0085200000000”
			$pushPhone = "00" . $areaCode . $phone;
		}

		$lmcu = $this->get("library_model_clean_userinfo");
		$phone_prev = '+' . $areaCode . '&nbsp;';
		$phoneAll = $phone_prev . $phone;
		if ($type == 1) {
			//判断是否注册
			if ($lmcu->isExistPhone($phoneAll)) {
				return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
			}
		} elseif ($type == 2) {
			// 判断有没有存在此手机账户
			$userInfoEntity = $lmcu->getUserInfoByPhone($phoneAll);
			if (empty($userInfoEntity)) {
				$userInfoEntity = $lmcu->getUserInfoByPhone($phone);
				if (empty($userInfoEntity)) {
					return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
				}
			}
		} elseif ($type == 3) //修改手机号
		{
			// 判断有没有存在此手机账户
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$userMachineInfo = $lmcu->getUserInfoByPhone($phoneAll);
			if ($userMachineInfo) {
				if ($userMachineInfo->getUserId() == $userId) {
					return new Response($this->getAPIResultJson("E02000", "您已经绑定当前手机号", ""));

				} else {
					return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
				}
			}
		} else {
			return new Response($this->getAPIResultJson("E02000", "数据错误", ''));
		}

		$code = PhoneMessageHandler::getCode();

		//记录手机验证码的各种信息
		$data = array("phone" => $pushPhone, "phoneCode" => $code, "time" => time());
		if (!$this->checkPhoneTimes($data)) {
			return new Response($this->getAPIResultJson("N00000", "发送验证码次数过多，请明天再试", ""));
		}

		$result = PhoneMessageHandler::VerifyWebCodeNew($pushPhone, $code);

		// 手机验证码验证
		if ($result) {
			$this->updateTXT($data);
			return new Response($this->getAPIResultJson("N00000", "验证码发送成功", ""));
		} else {
			return new Response($this->getAPIResultJson("E02000", "验证码获取失败，请勿频繁获取", ""));
		}
	}

	//注册手机号
	public function registerByPhoneAction() {
		try
		{

			$phone = $this->getParameter("phone");
			if (!$phone || !is_numeric($phone)) {
				return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
			}

			$phoneCode = $this->getParameter("phoneCode");
			if (!$phoneCode || !is_numeric($phoneCode) || strlen($phoneCode) != 6) {
				return new Response($this->getAPIResultJson("E02000", "验证码格式错误", ''));
			}

			$areaCode = $this->getParameter("areaCode");
			if (!$areaCode || !is_numeric($areaCode)) {
				return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
			}

			if ($areaCode == 86) {
				if (strlen($phone) != 11) {
					return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
				}
				$pushPhone = $phone;
			} else {
				//接收号码格式为00+国际区号+号码，如“0085200000000”
				$pushPhone = "00" . $areaCode . $phone;
			}

			$phone_prev = '+' . $areaCode . '&nbsp;';
			$phone = $phone_prev . $phone;

			if (!$this->checkPhoneCode($phoneCode, $pushPhone)) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ''));
			}

			$password = $this->getParameter("password");
			if (empty($password)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}
			$registerFrom = intval($this->getParameter("registerFrom"));
			$deviceToken = $this->getParameter("deviceToken");
			if ($deviceToken == "no&nbsp;device&nbsp;token") {
				$deviceToken = "";
			}
			$deviceNumber = $this->getParameter("deviceNumber");
			if (empty($deviceNumber)) {
				$deviceNumber = $deviceToken;
			}
			$ip = IPHandler::getClientIP();

			$lmcu = $this->get("library_model_clean_userinfo");

			if ($lmcu->isExistPhone($phone)) {
				return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
			}

			$userInfo = new UserInfoEntity();
			$userInfo->setPassword($password);
			$userInfo->setUserName($phone);
			$userInfo->setNickName($phone);

			$userInfo->setLastLoginIp($ip);
			$userInfo->setLoginCount(1);
			$userInfo->setPhone($phone);
			$userInfo->setRegisterFrom($registerFrom);

			$companyId = intval($this->getParameter("companyId"));
			if (empty($companyId)) {
				$companyId = 15;
			}
			$userInfo->setCompanyId($companyId);

			$sex = intval($this->getParameter("sex"));
			$userInfo->setSex($sex);

			$userId = $lmcu->addEntity($userInfo);
			$userInfo = $lmcu->getUserInfoByUserName($userInfo->getUserName());

			$data = $this->getToken($userInfo);
			if (!empty($data)) {
				return new Response($this->getAPIResultJson("N00000", "注册成功", $data));
			}
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("N00000", "注册失败", ""));
	}

	//编辑手机号
	public function editUserPhoneAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$phone = $this->getParameter("phone");
			if (!$phone || !is_numeric($phone)) {
				return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
			}

			$phoneCode = $this->getParameter("phoneCode");
			if (!$phoneCode || !is_numeric($phoneCode) || strlen($phoneCode) != 6) {
				return new Response($this->getAPIResultJson("E02000", "验证码格式错误", ''));
			}

			$areaCode = $this->getParameter("areaCode");
			if (!$areaCode || !is_numeric($areaCode)) {
				return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
			}

			LogHandler::writeLog(json_encode($data), "test/areaCode");

			if ($areaCode == 86) {
				if (strlen($phone) != 11) {
					return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
				}
				$pushPhone = $phone;
			} else {
				//接收号码格式为00+国际区号+号码，如“0085200000000”
				$pushPhone = "00" . $areaCode . $phone;
			}

			$phone_prev = '+' . $areaCode . '&nbsp;';
			$phone = $phone_prev . $phone;

			if (!$this->checkPhoneCode($phoneCode, $pushPhone)) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ''));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getEntity($userId);
			$userInfoEntity->setPhone($phone);
			$lmcu->editEntity($userInfoEntity);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//验证手机验证码
	public function checkPhoneCodeAction() {
		try
		{
			$phone = $this->getParameter("phone");
			if (!$phone || !is_numeric($phone)) {
				return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
			}

			$phoneCode = $this->getParameter("phoneCode");
			if (!$phoneCode || !is_numeric($phoneCode) || strlen($phoneCode) != 6) {
				return new Response($this->getAPIResultJson("E02000", "验证码格式错误", ''));
			}

			$areaCode = $this->getParameter("areaCode");
			if (!$areaCode || !is_numeric($areaCode)) {
				return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
			}

			if ($areaCode == 86) {
				if (strlen($phone) != 11) {
					return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
				}
				$pushPhone = $phone;
			} else {
				//接收号码格式为00+国际区号+号码，如“0085200000000”
				$pushPhone = "00" . $areaCode . $phone;
			}

			$phone_prev = '+' . $areaCode . '&nbsp;';
			$phone = $phone_prev . $phone;

			if (!$this->checkPhoneCode($phoneCode, $pushPhone)) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ''));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getUserInfoByPhone($phone);

			if (!$userInfoEntity) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			return new Response($this->getAPIResultJson("N00000", "校验成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//手机号密码
	public function forgetPasswordByPhoneAction() {
		try
		{
			$phone = $this->getParameter("phone");
			if (!$phone || !is_numeric($phone)) {
				return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
			}

			$phoneCode = $this->getParameter("phoneCode");
			if (!$phoneCode || !is_numeric($phoneCode) || strlen($phoneCode) != 6) {
				return new Response($this->getAPIResultJson("E02000", "验证码格式错误", ''));
			}

			$areaCode = $this->getParameter("areaCode");
			if (!$areaCode || !is_numeric($areaCode)) {
				return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
			}

			if ($areaCode == 86) {
				if (strlen($phone) != 11) {
					return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
				}
				$pushPhone = $phone;
			} else {
				//接收号码格式为00+国际区号+号码，如“0085200000000”
				$pushPhone = "00" . $areaCode . $phone;
			}

			$phone_prev = '+' . $areaCode . '&nbsp;';
			$phone = $phone_prev . $phone;

			if (!$this->checkPhoneCode($phoneCode, $pushPhone)) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ''));
			}

			$password = $this->getParameter("password");
			if (empty($password)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getUserInfoByPhone($phone);

			if (!$userInfoEntity) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			$userInfoEntity->setPassword(md5($password));
			$lmcu->editEntity($userInfoEntity);

			return new Response($this->getAPIResultJson("N00000", "密码修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	public function verifyEmailCodeAction() {
		try
		{
			$email = $this->getParameter("email");
			if (!$email || !$this->checkEmail($email)) {
				return new Response($this->getAPIResultJson("E02000", "请输入正确格式的邮箱", ""));
			}

			$type = intval($this->getParameter("type"));
			if (!$type) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			if ($type == 1) {
				if ($lmcu->isExistEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
				}

			} elseif ($type == 2) {
				if (!$lmcu->isExistEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
				}

			} elseif ($type == 3) {
				$userIdAES = $this->getParameter("userId");
				$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
				if (!is_int($userId) || $userId <= 0) {
					return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
				}
				if (!$this->validateLoginUser($userId)) {
					return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
				}

				$userMachineInfo = $lmcu->getUserInfoByEmail($email);
				if ($userMachineInfo) {
					if ($userMachineInfo->getUserId() == $userId) {
						return new Response($this->getAPIResultJson("E02000", "您已经绑定当前邮箱", ""));
					} else {
						return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
					}
				}

			} else {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			$code = PhoneMessageHandler::getCode();
			$result = $this->sendCodeEmail($code, $email);

			if ($result) {
				$data = array("email" => $email, "code" => $code, "time" => time());
				$this->updateEmailTXT($data);
				return new Response($this->getAPIResultJson("N00000", "验证码发送成功", ""));
			} else {
				return new Response($this->getAPIResultJson("E02000", "验证码获取失败，请勿频繁获取", ""));
			}

		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function registerByEmailAction() {
		try
		{
			$email = $this->getParameter("email");
			$password = $this->getParameter("password");
			$code = $this->getParameter("emailCode");
			if (!$email || !$this->checkEmail($email) || !$password || strlen($password) != 32 || !$code) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			if ($lmcu->isExistEmail($email)) {
				return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
			}

			$emailInfo = $this->getEmailInfo($email);
			if (!$emailInfo) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($email != $emailInfo["email"]) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($code != $emailInfo["code"]) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ""));
			}

			if (time() - $emailInfo["time"] > 30 * 60) {
				//验证码有效时间为30分钟
				return new Response($this->getAPIResultJson("E02000", "验证码失效", ""));
			}

			$registerFrom = intval($this->getParameter("registerFrom"));
			$ip = IPHandler::getClientIP();

			$userInfo = new UserInfoEntity();

			$userInfo->setEmail($email);
			$userInfo->setUserName($email);
			$userInfo->setNickName($email);
			$userInfo->setPassword(md5($password));

			$deviceToken = $this->getParameter("deviceToken");
			if ($deviceToken == "no&nbsp;device&nbsp;token") {
				$deviceToken = "";
			}
			$deviceNumber = $this->getParameter("deviceNumber");
			if (empty($deviceNumber)) {
				$deviceNumber = $deviceToken;
			}
			$userInfo->setLastLoginIp($ip);
			$userInfo->setLoginCount(1);
			$userInfo->setRegisterFrom($registerFrom);

			$companyId = intval($this->getParameter("companyId"));
			if (empty($companyId)) {
				$companyId = 15;
			}
			$userInfo->setCompanyId($companyId);

			$sex = intval($this->getParameter("sex"));
			if (empty($sex)) {
				$sex = 0;
			}
			$userInfo->setSex($sex);

			$userId = $lmcu->addEntity($userInfo);

			$userInfo = $lmcu->getUserInfoByEmail($userInfo->getEmail());
			$data = $this->getToken($userInfo);
			if (!empty($data)) {
				return new Response($this->getAPIResultJson("N00000", "注册成功", $data));
			}
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("N00000", "注册失败", ""));
	}

	//验证邮箱验证码
	public function checkEmailCodeAction() {
		try
		{
			$email = $this->getParameter("email");
			$code = $this->getParameter("emailCode");
			if (!$email || !$this->checkEmail($email) || !$code) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$emailInfo = $this->getEmailInfo($email);
			if (!$emailInfo) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($email != $emailInfo["email"]) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($code != $emailInfo["code"]) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ""));
			}

			if (time() - $emailInfo["time"] > 30 * 60) {
				//验证码有效时间为30分钟
				return new Response($this->getAPIResultJson("E02000", "验证码失效", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getUserInfoByEmail($email);

			if (!$userInfoEntity) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			return new Response($this->getAPIResultJson("N00000", "校验成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//邮箱找回密码
	public function forgetPasswordByEmailAction() {
		try
		{
			$email = $this->getParameter("email");
			$password = $this->getParameter("password");
			$code = $this->getParameter("emailCode");
			if (!$email || !$this->checkEmail($email) || !$password || strlen($password) != 32 || !$code) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$emailInfo = $this->getEmailInfo($email);
			if (!$emailInfo) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($email != $emailInfo["email"]) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($code != $emailInfo["code"]) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ""));
			}

			if (time() - $emailInfo["time"] > 30 * 60) {
				//验证码有效时间为30分钟
				return new Response($this->getAPIResultJson("E02000", "验证码失效", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getUserInfoByEmail($email);

			if (!$userInfoEntity) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			$userInfoEntity->setPassword(md5($password));
			$lmcu->editEntity($userInfoEntity);

			return new Response($this->getAPIResultJson("N00000", "密码修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//编辑邮箱
	public function editUserEmailAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$email = $this->getParameter("email");
			$code = $this->getParameter("emailCode");
			if (!$email || !$this->checkEmail($email) || !$code) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$emailInfo = $this->getEmailInfo($email);
			if (!$emailInfo) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($email != $emailInfo["email"]) {
				return new Response($this->getAPIResultJson("E02000", "请用此邮箱重新获取验证码", ""));
			}

			if ($code != $emailInfo["code"]) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ""));
			}

			if (time() - $emailInfo["time"] > 30 * 60) {
				//验证码有效时间为30分钟
				return new Response($this->getAPIResultJson("E02000", "验证码失效", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getEntity($userId);

			if (!$userInfoEntity) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			$userInfoEntity->setEmail($email);
			$lmcu->editEntity($userInfoEntity);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	//用微信号登陆
	public function loginByWeiXinAction() {
		try
		{
			$userName = $this->getParameter("userName");
			$avatar = $this->getParameter("avatar");
			$openId = $this->getParameter("openId");
			if (empty($userName) || empty($openId)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}
			$password = md5(md5(123));

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getUserInfoByOpenId($openId);

			if (!$userInfo) {
				$userInfo = new UserInfoEntity();
				$isNew = 1;
				$password = md5(md5(md5(123)));
				$userInfo->setPassword($password);
				$userInfo->setUserName($userName);
				$userInfo->setNickName($userName);
				$userInfo->setAvatar($avatar);
			} else {
				$isNew = 0;
				if (!$userInfo->getAvatar()) {
					$userInfo->setAvatar($avatar);
				}
			}

			$registerFrom = intval($this->getParameter("registerFrom"));
			$ip = IPHandler::getClientIP();

			$userInfo->setOpenId($openId);

			$deviceToken = $this->getParameter("deviceToken");
			if ($deviceToken == "no&nbsp;device&nbsp;token") {
				$deviceToken = "";
			}
			$deviceNumber = $this->getParameter("deviceNumber");
			if (empty($deviceNumber)) {
				$deviceNumber = $deviceToken;
			}

			$userInfo->setLastLoginIp($ip);
			$userInfo->setLoginCount(1);
			$userInfo->setRegisterFrom($registerFrom);

			$companyId = intval($this->getParameter("companyId"));
			if (empty($companyId)) {
				$companyId = 15;
			}
			$userInfo->setCompanyId($companyId);

			$sex = intval($this->getParameter("sex"));
			if (empty($sex)) {
				$sex = 0;
			}
			$userInfo->setSex($sex);

			if ($isNew) {
				$lmcu->addEntity($userInfo);

			} else {
				$lmcu->editEntity($userInfo);
				$isNew = 0;
			}

			$userInfo = $lmcu->getUserInfoByOpenId($openId);

			$data = $this->getToken($userInfo);
			if (!empty($data)) {
				$data["isNew"] = $isNew;
				return new Response($this->getAPIResultJson("N00000", "注册成功", $data));
			}
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
		return new Response($this->getAPIResultJson("E02000", "登陆失败", ""));
	}

	//用微信号登陆
	public function loginByFacebookAction() {
		try
		{
			$userName = $this->getParameter("userName");
			$avatar = $this->getParameter("avatar");
			$facebookId = $this->getParameter("facebookId");
			if (empty($userName) || empty($facebookId)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}
			$password = md5(md5(123));

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getUserInfoByFacebookId($facebookId);

			if (!$userInfo) {
				$userInfo = new UserInfoEntity();
				$isNew = 1;
				$password = md5(md5(md5(123)));
				$userInfo->setPassword($password);
				$userInfo->setUserName($userName);
				$userInfo->setNickName($userName);
				$userInfo->setAvatar($avatar);
			} else {
				$isNew = 0;
				if (!$userInfo->getAvatar()) {
					$userInfo->setAvatar($avatar);
				}
			}

			$registerFrom = intval($this->getParameter("registerFrom"));
			$ip = IPHandler::getClientIP();

			$userInfo->setFacebookId($facebookId);

			$deviceToken = $this->getParameter("deviceToken");
			if ($deviceToken == "no&nbsp;device&nbsp;token") {
				$deviceToken = "";
			}
			$deviceNumber = $this->getParameter("deviceNumber");
			if (empty($deviceNumber)) {
				$deviceNumber = $deviceToken;
			}

			$userInfo->setLastLoginIp($ip);
			$userInfo->setLoginCount(1);
			$userInfo->setRegisterFrom($registerFrom);

			$companyId = intval($this->getParameter("companyId"));
			if (empty($companyId)) {
				$companyId = 15;
			}
			$userInfo->setCompanyId($companyId);

			$sex = intval($this->getParameter("sex"));
			if (empty($sex)) {
				$sex = 0;
			}
			$userInfo->setSex($sex);

			if ($isNew) {
				$lmcu->addEntity($userInfo);

			} else {
				$lmcu->editEntity($userInfo);
				$isNew = 0;
			}

			$userInfo = $lmcu->getUserInfoByFacebookId($facebookId);

			$data = $this->getToken($userInfo);
			if (!empty($data)) {
				$data["isNew"] = $isNew;
				return new Response($this->getAPIResultJson("N00000", "注册成功", $data));
			}
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
		return new Response($this->getAPIResultJson("E02000", "登陆失败", ""));
	}

	//弹框完善手机号和密码
	public function addUserInfoAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!$userId) {
				$userId = intval($this->requestParameter("onlog"));
			}
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}

			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$phone = $this->getParameter("phone");
			if (!$phone || !is_numeric($phone)) {
				return new Response($this->getAPIResultJson("E02000", "手机号码格式错误", ''));
			}

			$phoneCode = $this->getParameter("phoneCode");
			if (!$phoneCode || !is_numeric($phoneCode) || strlen($phoneCode) != 6) {
				return new Response($this->getAPIResultJson("E02000", "验证码格式错误", ''));
			}

			$areaCode = $this->getParameter("areaCode");
			if (!$areaCode || !is_numeric($areaCode)) {
				return new Response($this->getAPIResultJson("E02000", "区号格式错误", ''));
			}

			if ($areaCode == 86) {
				if (strlen($phone) != 11) {
					return new Response($this->getAPIResultJson("E02000", "手机号码填写错误", ''));
				}
				$pushPhone = $phone;
			} else {
				//接收号码格式为00+国际区号+号码，如“0085200000000”
				$pushPhone = "00" . $areaCode . $phone;
			}

			$phone_prev = '+' . $areaCode . '&nbsp;';
			$phone = $phone_prev . $phone;

			if (!$this->checkPhoneCode($phoneCode, $pushPhone)) {
				return new Response($this->getAPIResultJson("E02000", "验证码错误", ''));
			}

			$password = $this->getParameter("password");
			if (empty($password)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getEntity($userId);
			if (empty($userInfo)) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$userInfo->setPassword(md5($password));
			$userInfo->setPhone($phone);
			$lmcu->editEntity($userInfo);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	private function sendCodeEmail($code, $email) {
		$message = \Swift_Message::newInstance()->setSubject($this->get("translator")->trans('小狗智能'))->setFrom('xiaogouzhineng@moomv.com', $this->get("translator")->trans('小狗智能邮件'))->setTo($email)->setBody($this->get("translator")->trans('验证码为') . " ：" . $code . " " . $this->get("translator")->trans('(30分钟内有效)'))->setEncoder(\Swift_Encoding::getBase64Encoding());
		//$result = $this->get('mailer')->send($message);

		$transport = \Swift_SmtpTransport::newInstance('smtp.qiye.163.com', "994", "ssl")
			->setUsername('xiaogouzhineng@moomv.com')
			->setPassword('gL6jx25yrusaqTLb');
		// 创建mailer对象
		$mailer = \Swift_Mailer::newInstance($transport);
		$result = $mailer->send($message);

		if ($result) {
			return time();
		} else {
			return false;
		}
	}

	private function checkUserName($userName) {
		$reg = "/[ #'\"]/";
		return preg_match($reg, $userName) === 0;
	}

	private function checkEmail($email) {
		$pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
		if (preg_match($pattern, $email)) {
			return true;
		} else {
			return false;
		}
	}

	//验证手机验证码
	private function checkPhoneCode($phoneCode, $phone) {
		if (!$phone || !$phoneCode) {
			return false;
		}

		$filename = ConfigHandler::getCommonConfig("phoneCodePath") . "/" . $phone . ".txt";
		$phoneData = FileCommonHandler::getArrayFromJsonFile($filename);
		if (!$phoneData) {
			return false;
		}

		if (time() >= $phoneData["time"] + 1200) {
			return false;
		}

		if ($phoneData["phone"] != $phone) {
			return false;
		}

		if ($phoneData["phoneCode"] != $phoneCode) {
			return false;
		}

		return true;
	}

	private function checkPhoneTimes($data) {
		$filename = ConfigHandler::getCommonConfig("phoneCodePath") . "/" . $data["phone"] . ".txt";
		$data = FileCommonHandler::getArrayFromJsonFile($filename);
		if (!$data) {
			return true;
		}

		if ($data["createTime"] == date("Y-m-d", time()) && $data["count"] >= 6) {
			return false;
		}

		return true;

	}

	private function updateTXT($data) {
		$data["createTime"] = date("Y-m-d", time());
		$filename = ConfigHandler::getCommonConfig("phoneCodePath") . "/" . $data["phone"] . ".txt";
		$oldData = FileCommonHandler::getArrayFromJsonFile($filename);

		if ($oldData) {
			if ($oldData["createTime"] == date("Y-m-d", time())) {
				if ($oldData["count"] >= 6) {
					return false;
				}
				$data["count"] = $oldData["count"] + 1;
			}
		} else {
			$data["count"] = 1;

		}
		FileCommonHandler::writeToFlie(json_encode($data), $filename);
		return true;
	}

	private function updateEmailTXT($data) {
		$data["createTime"] = date("Y-m-d", time());
		$filename = ConfigHandler::getCommonConfig("emailCodePath") . "/" . $data["email"] . ".txt";
		FileCommonHandler::writeToFlie(json_encode($data), $filename);
		return true;
	}

	private function getEmailInfo($email) {
		$filename = ConfigHandler::getCommonConfig("emailCodePath") . "/" . $email . ".txt";
		$data = FileCommonHandler::getArrayFromJsonFile($filename);

		return $data;
	}

}
?>