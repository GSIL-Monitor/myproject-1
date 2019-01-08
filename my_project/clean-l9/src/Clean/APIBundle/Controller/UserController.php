<?php

namespace Clean\APIBundle\Controller;

use Clean\APIBundle\Controller\BaseController;
use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\LogEntity;
use Clean\LibraryBundle\Entity\MachineMapEntity;
use Clean\LibraryBundle\Entity\PasswordTokenEntity;
use Clean\LibraryBundle\Entity\SystemMessageEntity;
use Clean\LibraryBundle\Entity\UserInfoEntity;
use Clean\LibraryBundle\Entity\UserInfoResult;
use Clean\LibraryBundle\Entity\UserMachineEntity;
use Common\Utils\AlexaHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\Crypt\AESCryptHandler;
use Common\Utils\File\UploadFileHandler;
use Common\Utils\IPHandler;
use Common\Utils\LogHandler;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController {
	//修改主人备注
	public function editUserNoteNameAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$sn = $this->getParameter("sn");
			$noteName = $this->getParameter("noteName");
			if (!$sn || !$noteName) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			$userMachineInfo = $lmcum->isExistUserSn($sn, $userId);
			if (empty($userMachineInfo)) {
				return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
			}
			if ($userMachineInfo->getUserType() != 1) {
				return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
			}

			$userMachineInfo->setNoteName($noteName);
			$lmcum->editEntity($userMachineInfo);

			return new Response($this->getAPIResultJson("N00000", "数据修改成功", $userMachineInfo));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("E02000", "数据修改失败", ""));
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

	public function registerAction() {
		try
		{
			$userName = $this->getParameter("userName");
			$email = $this->getParameter("email");
			$password = $this->getParameter("password");
			$phone = $this->getParameter("phone");

			$registerFrom = intval($this->getParameter("registerFrom"));
			$openId = $this->getParameter("openId");
			if ($openId && strlen($openId) > 10) {
				$password = md5(md5(123456));
			}

			if ((empty($phone) || empty($email) || empty($openId)) && empty($password)) {
				return new Response($this->getAPIResultJson("W02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");

			if (!empty($email)) {
				if (!$this->checkEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "请输入正确格式的邮箱", ""));
				}
				if ($lmcu->isExistEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
				}
				$userName = $email;
			}

			if (!empty($phone)) {
				if ($lmcu->isExistPhone($phone)) {
					return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
				}
				$userName = $phone;
			}

			if (empty($userName)) {
				return new Response($this->getAPIResultJson("W02000", "数据填写不完整", ""));
			}

			if (!$this->checkUserName($userName)) {
				return new Response($this->getAPIResultJson("E02000", "用户名非法", ""));
			}

			$deviceToken = $this->getParameter("deviceToken");
			if ($deviceToken == "no&nbsp;device&nbsp;token") {
				$deviceToken = "";
			}
			$deviceNumber = $this->getParameter("deviceNumber");
			if (empty($deviceNumber)) {
				$deviceNumber = $deviceToken;
			}

			$lmlt = $this->get("library_model_clean_logintoken");
			if ($lmlt->isDeviceRegistered($deviceNumber)) {
				LogHandler::writeLog(date("Y-m-d H:i:s") . "\t" . $phone . "\t" . IPHandler::getClientIP() . "\t" . $deviceNumber . "\r\n", "device");
				return new Response($this->getAPIResultJson("E02000", "该设备已被注册", ""));
			}

			$ip = IPHandler::getClientIP();
			if (IPHandler::checkIsBlackIP($ip)) {
				LogHandler::writeLog($ip . "\r\n", "blockip");
				return new Response($this->getAPIResultJson("E02000", "something is wrong", ""));
			}

			$userInfo = new UserInfoEntity();
			$avatar = $this->getParameter("avatar");
			if ($avatar) {
				$userInfo->setAvatar($avatar);
			}

			if ($openId) {
				$userInfo->setOpenId($openId);
			}

			$userInfo->setEmail($email);
			$userInfo->setPassword($password);
			$userInfo->setUserName($userName);
			$userInfo->setNickName($userName);

			$userInfo->setLastLoginIp($ip);
			$userInfo->setLoginCount(1);
			$userInfo->setPhone($phone);
			$userInfo->setRegisterFrom($registerFrom);

			$companyId = intval($this->getParameter("companyId"));
			if (empty($companyId)) {
				$companyId = 0;
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

	public function loginAction() {
		try
		{

			$userName = $this->getParameter("userName");
			$email = $this->getParameter("email");
			$phone = $this->getParameter("phone");

			$password = $this->getParameter("password");
			if (empty($userName) && empty($email) && empty($phone)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}
			if (empty($password) && strlen($password) != 32) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$password = md5($password);
			if ($userName) {
				$userInfo = $lmcu->getUserInfoByLogin($userName, $password);
			} elseif ($email) {
				$userInfo = $lmcu->getUserInfoByEmailAndPassword($email, $password);
			} elseif ($phone) {
				$userInfo = $lmcu->getUserInfoByPhoneAndPassword($phone, $password);
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}

			if (empty($userInfo)) {
				return new Response($this->getAPIResultJson("E02000", "用户信息错误", ""));
			}
			$openStatus = $userInfo->getOpenStatus();
			if ($openStatus != CommonDefine::OPEN_STATUS_TRUE) {
				return new Response($this->getAPIResultJson("E02000", "该用户已被禁止", ""));
			}

			$data = $this->getToken($userInfo);

			if (!empty($data)) {
				$userInfo->setLastLoginTime(new \DateTime());
				$userInfo->setLastLoginIp(IPHandler::getClientIP());
				$userInfo->setLoginCount($userInfo->getLoginCount() + 1);
				$lmcu->editEntity($userInfo);
				return new Response($this->getAPIResultJson("N00000", "登陆成功", $data));
			}
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
		return new Response($this->getAPIResultJson("E02000", "登陆失败", ""));
	}

	public function bindMachineAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$sn = $this->getParameter("sn");
			if (empty($sn)) {
				return new Response($this->getAPIResultJson("W02000", "数据填写不完整", ""));
			}
			$lmcm = $this->get("library_model_clean_machine");
			$snInfo = $lmcm->isExistSn($sn);
			if (!$snInfo) {
				return new Response($this->getAPIResultJson("E02000", "该sn无效", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			$userSnInfo = $lmcum->isExistUserSn($sn, $userId);
			if ($userSnInfo) {
				return new Response($this->getAPIResultJson("E02000", "该用户已经添加过此设备", $userSnInfo));
			}
			if (!$lmcum->isExistUserBySn($sn)) {
				$userType = 1;
			} else {
				$userType = 2;
			}

			$UserMachineEntity = new UserMachineEntity();
			$UserMachineEntity->setUserId($userId);
			$UserMachineEntity->setUserType($userType);
			$UserMachineEntity->setSn($sn);
			$userMachineId = $lmcum->addEntity($UserMachineEntity);

			//修改用户companyId
			if ($snInfo->getCompanyId() > 0) {
				$lmcu = $this->get("library_model_clean_userinfo");
				$userInfoEntity = $lmcu->getEntity($userId);
				$userInfoEntity->setCompanyId($snInfo->getCompanyId());
				$lmcu->editEntity($userInfoEntity);
			}
			return new Response($this->getAPIResultJson("N00000", "绑定成功", $userSnInfo));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function getUserMachineInfoAction() {
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

			$type = intval($this->getParameter("type"));
			if (!$type) {
				$type = 1;
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			if ($type == 1) {
//获取用户设备信息
				$result = $lmcum->getUserAllMachine($userId);
				if (empty($result)) {
					return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
				}
				for ($i = 0; $i < count($result); $i++) {
					$isOnline = $this->getRobotFileName($result[$i]->getSn());
					if ($isOnline) {
						$isOnline = 1;
					} else {
						$isOnline = 0;
					}
					$result[$i]->setIsOnline($isOnline);
				}
			} elseif ($type == 2) {
//获取设备用户信息
				$sn = $this->getParameter("sn");
				if (empty($sn)) {
					return new Response($this->getAPIResultJson("W02000", "数据填写不完整", ""));
				}
				$result = $lmcum->getMachineAllUser($sn);
				if (empty($result)) {
					return new Response($this->getAPIResultJson("N00000", "该设备暂无用户绑定", ""));
				}
			}

			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editUserInfoAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$type = intval($this->getParameter("type"));
			if (!$type) {
				$type = 1;
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getEntity($userId);
			if ($type == 1) {
				//修改名称或者邮箱
				$userName = $this->getParameter("userName");
				$nickName = $this->getParameter("nickName");
				if (!empty($userName)) {
					if (!$this->checkUserName($userName)) {
						return new Response($this->getAPIResultJson("E02000", "该用户名非法", ""));
					}
					if ($lmcu->isExistUserName($userName)) {
						return new Response($this->getAPIResultJson("E02000", "该用户名已被注册", ""));
					}
					$userInfoEntity->setUserName($userName);

				}

				if (!empty($nickName)) {
					$userInfoEntity->setNickName($nickName);
				}

				$email = $this->getParameter("email");
				if (!empty($email)) {
					if (!$this->checkEmail($email)) {
						return new Response($this->getAPIResultJson("E02000", "请输入正确格式的邮箱", ""));
					}
					if ($lmcu->isExistEmail($email)) {
						return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
					}
					$userInfoEntity->setEmail($email);

				}
				$phone = $this->getParameter("phone");
				if (!empty($phone)) {
					if ($lmcu->isExistPhone($phone)) {
						return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
					}
					$userInfoEntity->setPhone($phone);

				}
				$lmcu->editEntity($userInfoEntity);
				$data = $lmcu->getEntity($userId);
				return new Response($this->getAPIResultJson("N00000", "数据修改成功", $data));
			} elseif ($type == 2) {
				//修改密码
				$newPassword = $this->getParameter("newPassword");
				$oldPassword = $this->getParameter("oldPassword");
				if (empty($newPassword) || empty($oldPassword)) {
					return new Response($this->getAPIResultJson("W02000", "数据填写不完整", ""));
				}
				if (md5($oldPassword) != $userInfoEntity->getPassword()) {
					return new Response($this->getAPIResultJson("E02000", "原密码输入错误", ""));
				}
				$userInfoEntity->setPassword(md5($newPassword));
				$lmcu->editEntity($userInfoEntity);
				$data = $lmcu->getEntity($userId);
				return new Response($this->getAPIResultJson("N00000", "数据修改成功", $data));
			}
			return new Response($this->getAPIResultJson("N00000", "数据修改失败", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("E02000", "数据修改失败", ""));
	}

	public function getUserInfoAction() {
		try
		{
			$userIdAES = $this->getParameter("userId");
			//$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
			// if (!$userId) {
			// 	$userId = intval($this->requestParameter("onlog"));
			// }
			$userId = intval($this->requestParameter("onlog"));
			if (!is_int($userId) || $userId <= 0) {
				return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
			}
			if (!$this->validateLoginUser($userId)) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getEntity($userId);
			if (empty($userInfoEntity)) {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$companyId = $userInfoEntity->getCompanyId();
			if (!$companyId) {
				$companyId = -1;
			}
			$result = $lmcsm->getUnReadMessageList($userInfoEntity->getLastSystemMessageId(), $companyId, $userId);
			if ($result) {
				$unRead = 1;
			} else {
				$unRead = 0;
			}
			$result = new UserInfoResult();
			$result->setUserId($userInfoEntity->getUserId());
			$result->setNickName($userInfoEntity->getNickName());
			$result->setUserName($userInfoEntity->getUserName());
			$result->setEmail($userInfoEntity->getEmail());
			$result->setPhone($userInfoEntity->getPhone());
			$result->setAvatar($userInfoEntity->getAvatar());
			$result->setNowSn($userInfoEntity->getNowSn());
			$result->setCompanyId($userInfoEntity->getCompanyId());
			$result->setCreateTime($userInfoEntity->getCreateTime());
			$result->setSex($userInfoEntity->getSex());
			$result->setNowSn($userInfoEntity->getNowSn());
			$result->setUnRead($unRead);
			if ($userInfoEntity->getNowSn()) {
				//获取机器名称
				$lmcm = $this->get("library_model_clean_machine");
				$machineEntity = $lmcm->getMachineBySn($userInfoEntity->getNowSn());
				$machineName = $machineEntity->getMachineName();
				if ($machineName) {
					$result->setMachineName($machineName);
				}

				$isOnline = $this->getRobotFileName($userInfoEntity->getNowSn());
				if ($isOnline) {
					$result->setIsOnline(true);
				} else {
					$result->setIsOnline(false);
				}

			}

			$result->setIsStartBind(intval($userInfoEntity->getIsStartBind()));

			return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	public function editUserMachineAction() {
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
			$type = intval($this->getParameter("type"));
			if (!$type) {
				$type = 1;
			}
			$sn = $this->getParameter("sn");
			if (empty($sn)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcum = $this->get("library_model_clean_usermachine");
			$userMachineInfo = $lmcum->isExistUserSn($sn, $userId);
			if (empty($userMachineInfo)) {
				return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
			}
			if ($type == 1) {
				//修改备注名
				$machineName = $this->getParameter("machineName");
				if (empty($machineName)) {
					return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
				}

				if ($userMachineInfo->getUserType() != 1) {
					return new Response($this->getAPIResultJson("E02000", "抱歉，您不是该设备主人，暂无修改权限", ""));
				}
				$lmcm = $this->get("library_model_clean_machine");
				$machineEntity = $lmcm->getMachineBySn($sn);
				$machineEntity->setMachineName($machineName);
				$lmcm->editEntity($machineEntity);
				$data = $lmcum->getUserAllMachine($userId);
				return new Response($this->getAPIResultJson("N00000", "数据修改成功", $data));
			} elseif ($type == 2) {
				$lmcsm = $this->get("library_model_clean_systemmessage");
				$lmcu = $this->get("library_model_clean_userinfo");
				$userInfoEntity = $lmcu->getEntity($userId);
				$companyId = $userInfoEntity->getCompanyId();

				//删除设备
				if ($userMachineInfo->getUserType() == 1) {
//是设备主人，则删除所有拥有该设备的用户

					$entityList = $lmcum->getEntityBySn($sn);
					$result = $lmcum->deleteAllUserMachine($sn);
					//删除该设备中所有用户
					$this->deleteAllUser($sn);

					for ($i = 0; $i < count($entityList); $i++) {
						$toUserId = $entityList[$i]->getUserId();
						//修改订阅SN
						$userNewEntity = $lmcu->getEntity($toUserId);
						if ($userNewEntity && $userNewEntity->getNowSn() == $sn) {
							$newSn = $lmcum->getOneEntityByUserId($toUserId);
							if ($newSn && $newSn->getSn()) {
								$newSn = $newSn->getSn();
							} else {
								$newSn = "";
							}
							$userNewEntity->setNowSn($newSn);
							$lmcu->editEntity($userNewEntity);

							//在新设备中添加该用户
							if ($newSn) {
								$this->addUserBySn($newSn);
							}
						}
						//插入消息表
						$entity = new SystemMessageEntity();
						$entity->setCompanyId($companyId);
						$entity->setTitle("解绑扫地机");
						$content = "您已经解绑扫地机,SN:" . $sn;
						$entity->setMessageContent($content);
						$entity->setMessageType("1");
						$entity->setFromUserId($userId);
						$entity->setToUserId($toUserId);
						$systemMessageId = $lmcsm->addEntity($entity);
					}

				} elseif ($userMachineInfo->getUserType() == 2) {
					$result = $lmcum->deleteEntity($userMachineInfo->getUserMachineId());
					//修改订阅SN
					if ($userInfoEntity->getNowSn() == $sn) {
						$newSn = $lmcum->getOneEntityByUserId($userId);
						if ($newSn && $newSn->getSn()) {
							$newSn = $newSn->getSn();
						} else {
							$newSn = "";
						}
						$userInfoEntity->setNowSn($newSn);
						$lmcu->editEntity($userInfoEntity);

						//删除该设备中该用户
						$this->deleteUserBySn($sn);

						//在新设备中添加该用户
						if ($newSn) {
							$this->addUserBySn($newSn);
						}
					}

					//添加到消息表
					$lmcsm = $this->get("library_model_clean_systemmessage");
					$entity = new SystemMessageEntity();
					$entity->setCompanyId($companyId);
					$entity->setTitle("解绑扫地机");
					$content = "您已经解绑扫地机,SN:" . $sn;
					$entity->setMessageContent($content);
					$entity->setMessageType("1");
					$entity->setFromUserId($userId);
					$entity->setToUserId($userId);
					$systemMessageId = $lmcsm->addEntity($entity);

				}
				$data = $lmcum->getUserAllMachine($userId);
				return new Response($this->getAPIResultJson("N00000", "数据删除成功", $data));
			}
			return new Response($this->getAPIResultJson("E02000", "数据修改失败", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("E02000", "数据修改失败", ""));
	}

	public function editSharerUserInfoAction() {
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
			$sn = $this->getParameter("sn");
			$shareUserId = $this->getParameter("shareUserId");
			if (empty($sn) || empty($shareUserId)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$type = intval($this->getParameter("type"));
			if (!$type) {
				$type = 1;
			}

			$lmcum = $this->get("library_model_clean_usermachine");
			$userMachineInfo = $lmcum->isExistUserSn($sn, $userId);
			if (empty($userMachineInfo)) {
				return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
			}
			if ($userMachineInfo->getUserType() != 1) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，您不是该设备主人，暂无修改权限", ""));
			}
			if ($type == 1) {
				//修改共享者备注名
				$noteName = $this->getParameter("noteName");
				if (empty($noteName)) {
					return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
				}
				$shareUserInfo = $lmcum->getEntityByUserIdAndSn($shareUserId, $sn);
				if (empty($shareUserInfo) || $shareUserInfo->getUserType() == 1) {
					return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
				}
				$shareUserInfo->setNoteName($noteName);
				$lmcum->editEntity($shareUserInfo);
				$data = $lmcum->getMachineAllUser($sn);
				return new Response($this->getAPIResultJson("N00000", "数据修改成功", $data));
			} elseif ($type == 2) {
				//删除共享者
				$shareUserInfo = $lmcum->getEntityByUserIdAndSn($shareUserId, $sn);
				if (empty($shareUserInfo) || $shareUserInfo->getUserType() == 1) {
					return new Response($this->getAPIResultJson("E02000", "管理员不能单独删除自己", ""));
				}

				//删除该设备中该用户
				$lmcum->deleteEntity($shareUserInfo->getUserMachineId());

				//删除socket设备中该用户
				$this->deleteUserBySn($sn);

				//修改订阅消息
				$lmcu = $this->get("library_model_clean_userinfo");
				$userInfoEntity = $lmcu->getEntity($shareUserId);
				if ($userInfoEntity && $userInfoEntity->getNowSn() == $sn) {
					$newSn = $lmcum->getOneEntityByUserId($shareUserId);
					if ($newSn && $newSn->getSn()) {
						$newSn = $newSn->getSn();
					} else {
						$newSn = "";
					}
					$userInfoEntity->setNowSn($newSn);
					$lmcu->editEntity($userInfoEntity);
				}

				$data = $lmcum->getMachineAllUser($sn);

				//添加到消息表
				$lmcsm = $this->get("library_model_clean_systemmessage");
				$entity = new SystemMessageEntity();
				$entity->setCompanyId($userInfoEntity->getCompanyId());
				$entity->setTitle("解绑扫地机");
				$content = "您已经解绑扫地机,SN:" . $sn;
				$entity->setMessageContent($content);
				$entity->setMessageType("1");
				$entity->setFromUserId($userId);
				$entity->setToUserId($userInfoEntity->getUserId());
				$systemMessageId = $lmcsm->addEntity($entity);

				return new Response($this->getAPIResultJson("N00000", "数据删除成功", $data));
			}
			return new Response($this->getAPIResultJson("E02000", "数据修改失败", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("N00000", "数据修改失败", ""));
	}

	public function addSharerUserAction() {
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
			$sn = $this->getParameter("sn");
			if (empty($sn)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcum = $this->get("library_model_clean_usermachine");
			$userMachineInfo = $lmcum->isExistUserSn($sn, $userId);
			if (empty($userMachineInfo)) {
				return new Response($this->getAPIResultJson("E02000", "暂无数据", ""));
			}
			if ($userMachineInfo->getUserType() != 1) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，您不是该设备主人，暂无添加权限", ""));
			}

			$userName = $this->getParameter("userName");
			$email = $this->getParameter("email");
			$phone = $this->getParameter("phone");
			if (empty($userName) && empty($email) && empty($phone)) {
				return new Response($this->getAPIResultJson("E02000", "缺少数据", ""));
			}
			$lmcu = $this->get("library_model_clean_userinfo");
			if ($userName) {
				$userInfoEntity = $lmcu->isExistUserName($userName);
			} elseif ($email) {
				$userInfoEntity = $lmcu->isExistEmail($email);
			} elseif ($phone) {
				$userInfoEntity = $lmcu->isExistPhone($phone);
			}

			if (empty($userInfoEntity)) {
				return new Response($this->getAPIResultJson("E02000", "抱歉，该用户不存在", ""));
			}

			$shareUserInfo = $lmcum->isExistUserSn($sn, $userInfoEntity->getUserId());
			if ($shareUserInfo) {
				return new Response($this->getAPIResultJson("E02000", "该用户已存在，请勿重复添加", ""));
			}

			//添加订阅
			$nowSn = $userInfoEntity->getNowSn();
			if (!$nowSn || strlen($nowSn) < 2) {
				$userInfoEntity->setNowSn($sn);
				$lmcu->editEntity($userInfoEntity);
			}

			$entity = new UserMachineEntity();
			$entity->setUserId($userInfoEntity->getUserId());
			$entity->setSn($sn);
			$entity->setUserType(2);
			$lmcum->addEntity($entity);
			$data = $lmcum->getMachineAllUser($sn);

			//修改socket保存文件
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
			$filename = $this->getRobotFileName($sn);
			if ($filename) {
				//获取文件的内容
				$arr = $this->getRobot($filename);
				//修改文件的内容
				if ($arr) {
					$arr["userList"] = $robotUserList;
					$fp = fopen($filename, "w");
					fwrite($fp, json_encode($arr));
					fclose($fp);
				}
			}

			//添加到消息表
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$entity = new SystemMessageEntity();
			$entity->setCompanyId($userInfoEntity->getCompanyId());
			$entity->setTitle("绑定扫地机");
			$content = "您已经绑定扫地机,SN:" . $sn;
			$entity->setMessageContent($content);
			$entity->setMessageType("1");
			$entity->setFromUserId($userId);
			$entity->setToUserId($userInfoEntity->getUserId());
			$systemMessageId = $lmcsm->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "数据添加成功", $data));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

	}

	public function exchangeMachineAction() {
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
		$sn = $this->getParameter("sn");
		$filename = $this->getRobotFileName($sn);

		//修改订阅消息
		$lmcu = $this->get("library_model_clean_userinfo");
		$lmcum = $this->get("library_model_clean_usermachine");

		$userInfoEntity = $lmcu->getEntity($userId);
		$historyNowSn = $userInfoEntity->getNowSn();

		if ($sn == $historyNowSn) {
			return new Response($this->getAPIResultJson("N00000", "切换成功", ""));
		}

		if ($userInfoEntity) {
			$userInfoEntity->setNowSn($sn);
			$lmcu->editEntity($userInfoEntity);
		}

		//只需要删除当前订阅的SN，即可
		if ($historyNowSn) {
			$this->deleteUserBySn($historyNowSn);
		}

		$tem = 1;
		if (!$filename) {
			$tem = false;
		} else {
			$this->addUserBySn($sn);
		}

		// $userRobotInfo = $lmcum->getEntityByUserId($userId);

		// for ($i=0; $i < count($userRobotInfo); $i++)
		// {
		//     if($userRobotInfo[$i]->getSn() != $sn)
		//     {
		//         $this->deleteUserBySn($userRobotInfo[$i]->getSn());
		//     }
		// }

		if (!$tem) {
			return new Response($this->getAPIResultJson("N00000", "当前设备不在线", ""));

		}
		return new Response($this->getAPIResultJson("N00000", "切换成功", ""));

	}

	//扫地机发起的绑定设备
	public function bindAction() {
		//绑定删除所有用户，自己是管理者
		if (!$this->validateCleanMachine()) {
			return new Response($this->getResultJson("250", "Authorization failed", ""));
		}

		$userId = intval($this->requestParameter("qid"));
		$sn = $this->requestParameter("sn");

		if (!is_int($userId) || $userId <= 0) {
			return new Response($this->getResultJson("100", "The data you entered is not complete", ""));
		}

		if (empty($sn)) {
			return new Response($this->getResultJson("100", "The data you entered is not complete", ""));
		}

		$lmcm = $this->get("library_model_clean_machine");
		$snInfo = $lmcm->isExistSn($sn);
		if (!$snInfo) {
			return new Response($this->getResultJson("250", "该sn无效", ""));
		}
		$lmcum = $this->get("library_model_clean_usermachine");

		//删除该sn所有绑定者
		$entityList = $lmcum->getEntityBySn($sn);
		$result = $lmcum->deleteAllUserMachine($sn);
		$lmcsm = $this->get("library_model_clean_systemmessage");
		$lmcu = $this->get("library_model_clean_userinfo");
		for ($i = 0; $i < count($entityList); $i++) {
			$toUserId = $entityList[$i]->getUserId();
			//修改订阅SN
			$userNewEntity = $lmcu->getEntity($toUserId);
			if ($userNewEntity && $userNewEntity->getNowSn() == $sn) {
				$newSn = "";
				$userNewEntity->setNowSn($newSn);
				$lmcu->editEntity($userNewEntity);
			}
			//插入消息表
			$entity = new SystemMessageEntity();
			$entity->setCompanyId($userNewEntity->getCompanyId());
			$entity->setTitle("解绑扫地机");
			$content = "您已经解绑扫地机,SN:" . $sn;
			$entity->setMessageContent($content);
			$entity->setMessageType("1");
			$entity->setFromUserId($userId);
			$entity->setToUserId($toUserId);
			$systemMessageId = $lmcsm->addEntity($entity);
		}

		$userSnInfo = $lmcum->isExistUserSn($sn, $userId);
		if ($userSnInfo) {
			$userSnInfo->setUserType(1);
			$lmcum->editEntity($userSnInfo);
		} else {
			$UserMachineEntity = new UserMachineEntity();
			$UserMachineEntity->setUserId($userId);
			$UserMachineEntity->setUserType(1);
			$UserMachineEntity->setSn($sn);
			$userMachineId = $lmcum->addEntity($UserMachineEntity);
		}

		//修改用户订阅sn
		$lmcu = $this->get("library_model_clean_userinfo");
		$userInfoEntity = $lmcu->getEntity($userId);

		$historyNowSn = $userInfoEntity->getNowSn();
		if ($historyNowSn) {
			$this->deleteUserBySn($historyNowSn);
		}

		$userInfoEntity->setNowSn($sn);

		//修改用户companyId
		if ($snInfo->getCompanyId() > 0) {
			$userInfoEntity->setCompanyId($snInfo->getCompanyId());
		}
		$lmcu->editEntity($userInfoEntity);

		$this->addUserBySn($sn);

		//通知APP绑定成功
		$pushData = array(
			"infoType" => 23003,
			"connectionType" => 1,
			"deviceType" => 3,
		);
		$pushData["data"] = array(
			"bindStatus" => 1,
			"sn" => $sn,
		);
		$pushData["extend"] = array("taskid" => time(), "userId" => $userId);
		$pushData = json_encode($pushData, JSON_UNESCAPED_SLASHES) . "#\t#";
		$res = AlexaHandler::swooleClient($pushData, 9501);

		for ($i = 0; $i < 3; $i++) {
			//多推送几次成功给APP
			sleep(1);
			$res = AlexaHandler::swooleClient($pushData, 9501);
		}

		return new Response($this->getResultJson("0", "成功", $sn));
	}

	//在设备中添加该用户，重新获取
	private function addUserBySn($sn) {
		//获取文件的内容
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			$arr = $this->getRobot($filename);
			$lmcum = $this->get("library_model_clean_usermachine");
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = $robotUserList;
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	//在设备中删除该用户
	private function deleteUserBySn($sn) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			//获取文件的内容
			$arr = $this->getRobot($filename);
			$lmcum = $this->get("library_model_clean_usermachine");
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = $robotUserList;
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	private function deleteAllUser($sn) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			//获取文件的内容
			$arr = $this->getRobot($filename);
			//修改文件的内容
			if ($arr) {
				unset($arr["userList"]);
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	/*
		     * 获取机器是否在线
	*/
	private function getRobotFileName($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/robots/" . $subDir;

		$filename = $dir . "/" . $sn;
		if (!file_exists($filename)) {
			return false;
		}
		$content = $this->getRobot($filename);
		$fd = intval($content["fd"]);
		if (!$content || !$fd) {
			unlink($filename);
			return false;
		}

		$fdSubDir = substr(strval($fd), 0, 1);
		$fdFilename = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/fd/" . $fdSubDir . "/" . $fd;

		if (!file_exists($fdFilename)) {
			unlink($filename);
			return false;
		}

		return $filename;
	}

	//获取文件内容
	private function getRobot($filename) {
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$result = json_decode($content, true);

			return $result;
		}
		return null;
	}

	public function uploadCleanFileAction() {
		try {

			//权限验证
			// $token = $this->requestParameter("token");
			// if(!$this->validateMachine($token))
			// {
			//     return new Response($this->getAPIResultJson("E03000", "fail", ""));
			// }

			if (!$this->validateCleanMachine()) {
				return new Response($this->getResultJson("102", "你的sid过期啦", ""));
			}

			$filePath = ConfigHandler::getCommonConfig("cleanPath");
			$fileRes = UploadFileHandler::requestUpload("cleanFile", $filePath, true);

			//扫地机上传错误日志
			if (is_array($fileRes) && isset($fileRes[3]) && $fileRes[3] == "log") {
				$res = array();
				$content = $fileRes[0];
				$temArr = explode("|", substr($content, 1));
				for ($i = 0; $i < count($temArr) / 6; $i++) {
					$tem = new LogEntity();
					$tem->setSn($fileRes[1]); // sn
					$tem->setTime($temArr[$i * 6]);
					$tem->setEvent($temArr[$i * 6 + 1]);
					$tem->setWorkType($temArr[$i * 6 + 2]);
					$tem->setLevelNumber($temArr[$i * 6 + 3]);
					$tem->setLocation($temArr[$i * 6 + 4]);
					$tem->setMessage($temArr[$i * 6 + 5]);
					array_push($res, $tem);
				}
				$lmcl = $this->get("library_model_clean_log");
				$lmcl->addBatchEntity($res);
				return new Response($this->getResultJson("0", "success", ""));
			}

			if (!is_array($fileRes) && strpos($fileRes, "@") > 0) {
				return new Response($this->getResultJson("100", "fail", ""));
			}

			$fileName = $fileRes["fileName"];
			$url = str_replace($filePath, "", $fileName);
			$cleanUrl = ConfigHandler::getCommonConfig("cleanUrl");
			$url = $cleanUrl . $url;

			$backupMapMd5 = $this->requestParameter("backupMapMd5");
			if ($backupMapMd5) {
				$fileRes = UploadFileHandler::requestUpload("backupMap", $filePath, true);

				//如果是地图，则插入数据库
				if (isset($fileRes["map"]) && $fileRes["map"]) {

					$sn = $this->requestParameter("sn");
					$deviceType = $this->requestParameter("devType");
					$createtime = $this->requestParameter("createtime"); // 时间

					if (!$sn || !$backupMapMd5 || !$createtime) {
						return new Response($this->getResultJson("100", "The data you entered is not complete", ""));
					}

					$machineMapEntity = new MachineMapEntity();
					$machineMapEntity->setSn($sn);
					$machineMapEntity->setBackupMd5($backupMapMd5);
					$createtime = new \DateTime(date("Y-m-d H:i:s", $createtime));
					$machineMapEntity->setCreatetime($createtime);

					$fileName = $fileRes["fileName"];
					$url = $cleanUrl . str_replace($filePath, "", $fileName);
					$machineMapEntity->setUrl($url);

					$lmcmm = $this->get("library_model_clean_machinemap");
					$lmcmm->addEntity($machineMapEntity);
				}
			}

			return new Response($this->getResultJson("0", "success", $url));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getResultJson("214", "文件上传失败", ""));
		}
	}

	public function uploadAvatarFileAction() {
		$userIdAES = $this->getParameter("userId");
		$userId = intval(AESCryptHandler::decrypt($userIdAES, CommonDefine::AES_KEY, CommonDefine::AES_IV));
		if (!is_int($userId) || $userId <= 0) {
			return new Response($this->getAPIResultJson("E03000", "缺少重要参数", ""));
		}
		if (!$this->validateLoginUser($userId)) {
			return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
		}
		$filePath = ConfigHandler::getCommonConfig("avatarPath");
		$fileResult = UploadFileHandler::requestUploadTypeFile("avatarFile", $filePath, true);
		if (!is_array($fileResult)) {
			return new Response($this->getAPIResultJson("E02000", $fileResult, ""));
		}
		$filename = $fileResult["fileName"];
		$url = str_replace($filePath, "", $filename);
		$avatarUrl = ConfigHandler::getCommonConfig("avatarUrl");
		$url = $avatarUrl . $url;
		$lmcu = $this->get("library_model_clean_userinfo");
		$userInfoEntity = $lmcu->getEntity($userId);
		$userInfoEntity->setAvatar($url);
		$lmcu->editEntity($userInfoEntity);
		return new Response($this->getAPIResultJson("N00000", "上传成功", $url));
	}

	public function getUserPasswordAction() {
		try
		{
			if (!$this->validateResetPassword()) {
				return new Response($this->getAPIResultJson("E03000", "权限验证失败", ""));
			}
			$lmcu = $this->get("library_model_clean_userinfo");
			$type = intval($this->getParameter("type"));
			if ($type <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			if ($type == 1) {
				//手机号码
				$phone = $this->getParameter("phone");
				if (!$phone) {
					return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
				}
				$result = $lmcu->getUserInfoByPhone($phone);
			} else {
				$email = $this->getParameter("email");
				if (!$email) {
					return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
				}
				$result = $lmcu->getUserInfoByEmail($email);
			}

			if (!$result) {
				return new Response($this->getAPIResultJson("N00000", "抱歉，该用户不存在", ""));
			}

			$email = $result->getEmail();
			if (!$email || !$this->checkEmail($email)) {
				return new Response($this->getAPIResultJson("N00000", "请先填写邮箱", ""));
			}
			$lmcpt = $this->get("library_model_clean_passwordtoken");
			$passwordTokenInfo = $lmcpt->getPasswordTokenByEmail($email);

			if (!$passwordTokenInfo) {
				$token = md5(md5(time()));
				$expiredTime = new \Datetime(date("Y-m-d H:i:s", strtotime("+3 hours")));

				$entity = new PasswordTokenEntity();
				$entity->setEmail($email);
				$entity->setToken($token);
				$entity->setExpiredTime($expiredTime);

				$lmcpt->addEntity($entity);
			} else {
				$expiredTime = $passwordTokenInfo->getExpiredTime();
				$expiredTime = $expiredTime->format("U");

				if ($expiredTime > time()) {
					return new Response($this->getAPIResultJson("N00000", "请勿重复提交，已发往您邮箱", array()));
				} else {
					$expiredTime = new \Datetime(date("Y-m-d H:i:s", strtotime("+3 hours")));
					$passwordTokenInfo->setExpiredTime($expiredTime);
					$token = md5(md5(time()));
					$passwordTokenInfo->setToken($token);
					$lmcpt->editEntity($passwordTokenInfo);
				}

			}

			$domain = ConfigHandler::getCommonConfig("domain");

			$message = \Swift_Message::newInstance()->setSubject($this->get("translator")->trans('用户密码修改'))->setFrom('robotappservice@126.com', $this->get("translator")->trans('扫地机'))->setTo($email)->setBody($this->renderView('CleanAPIBundle:User:forgetpassword.html.twig', array(
				'email' => $email,
				"token" => $token,
				"applyDate" => date("Y-m-d H:m:s", time()),
				"domain" => $domain,
			)), 'text/html')->setEncoder(\Swift_Encoding::getBase64Encoding());
			//$this->get('mailer')->send($message);

			$transport = \Swift_SmtpTransport::newInstance('smtp.126.com', "465", "ssl")
				->setUsername('robotappservice@126.com')
				->setPassword('lexing123');
			// 创建mailer对象
			$mailer = \Swift_Mailer::newInstance($transport);
			$result = $mailer->send($message);

			return new Response($this->getAPIResultJson("N00000", "重置密码链接已经发送到您邮箱"));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function resetPasswordPageAction() {
		try
		{
			$email = $this->requestParameter("email");
			$token = $this->requestParameter("token");

			return $this->render('CleanAPIBundle:User:resetpassword.html.twig', array(
				'token' => $token,
				'email' => $email,
			));
		} catch (Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

	public function resetPasswordAction() {
		try
		{
			$email = $this->requestParameter("email");
			$token = $this->requestParameter("token");
			if (empty($email) || empty($token)) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			if (!$this->checkEmail($email)) {
				return new Response($this->getAPIResultJson("E02000", "邮箱错误", ""));
			}

			$password = $this->requestParameter("password");
			$confirmPassword = $this->requestParameter("confirmPassword");

			if (empty($password) || $password != $confirmPassword) {
				return new Response($this->getAPIResultJson("E02000", "密码输入错误", ""));
			}

			$lmcpt = $this->get("library_model_clean_passwordtoken");
			$passwordTokenInfo = $lmcpt->getPasswordTokenByEmail($email);

			if (!$passwordTokenInfo) {
				return new Response($this->getAPIResultJson("E02000", "链接无效", ""));
			}

			$expiredTime = $passwordTokenInfo->getExpiredTime();
			$expiredTime = $expiredTime->format("U");

			if (time() > $expiredTime) {
				return new Response($this->getAPIResultJson("E02000", "链接无效", ""));
			}

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfo = $lmcu->getUserInfoByEmail($email);
			$userInfo->setPassword(md5(md5(md5($password))));
			$lmcu->editEntity($userInfo);
			return new Response($this->getAPIResultJson("N00000", "修改成功", ""));
		} catch (\Exception $ex) {
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}
	}

}
?>