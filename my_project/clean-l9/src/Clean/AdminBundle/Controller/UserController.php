<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\UserInfoEntity;
use Clean\LibraryBundle\Entity\UserInfoResult;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Common\Utils\HtmlHandler;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController {

	public function userPageListAction() {
		if ($this->CompanyId == -1) {
			$isAdmin = true;
		} else {
			$isAdmin = false;
		}
		$lmcc = $this->get("library_model_clean_company");
		$companyInfo = $lmcc->getEntityList();
		return $this->render("CleanAdminBundle:User:userPageList.html.twig", array(
			"companyInfo" => $companyInfo,
			"isAdmin" => $isAdmin,
		));
	}

	public function getUserPageListAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$lmcu = $this->get("library_model_clean_userinfo");

			$searchType = $this->requestParameter("searchType");
			$keyword = $this->requestParameter("keyWord");
			$startDate = $this->requestParameter("startDate");
			$endDate = $this->requestParameter("endDate");
			$sn = $this->requestParameter("sn");
			$userAppVersion = $this->requestParameter("userAppVersion");
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			// if($companyId == -1)
			// {
			// 	$companyId=intval($this->requestParameter("companyId"));
			// 	$result = $lmcu->getPageUserAndCompany($pageIndex,$pageSize,$companyId,$searchType,$keyword,$startDate,$endDate,$sn);
			// }else
			// {
			// 	$result = $lmcu->getPageUser($pageIndex,$pageSize,$companyId,$searchType,$keyword,$startDate,$endDate,$sn);
			// }

			if ($companyId == -1) {
				$companyId = intval($this->requestParameter("companyId"));
			}
			$result = $lmcu->getPageUserAndCompany($pageIndex, $pageSize, $companyId, $searchType, $keyword, $startDate, $endDate, $sn, $userAppVersion);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addUserAction() {
		try
		{
			if ($this->CompanyId == -1) {
				$isAdmin = true;
			} else {
				$isAdmin = false;
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			return $this->render("CleanAdminBundle:User:addUser.html.twig", array("companyInfo" => $companyInfo, "isAdmin" => $isAdmin));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addUserSubmitAction() {
		try
		{

			if ($this->CompanyId == -1) {
				$companyId = intval($this->requestParameter("companyId"));
			} else {
				//当前管理员
				$companyId = $this->CompanyId;
			}

			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$userName = $this->requestParameter("userName");
			$email = $this->requestParameter("email");
			$phone = $this->requestParameter("phone");
			$password = $this->requestParameter("password");
			$sex = intval($this->requestParameter("sex"));
			$avatar = $this->requestParameter("avatar");
			$registerFrom = 3;
			$lmcu = $this->get("library_model_clean_userinfo");

			if (!$password || !$phone || !$userName) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$entity = new UserInfoEntity();
			if ($sex > 0) {
				$entity->setSex($sex);
			}
			if ($lmcu->isExistUserName($userName)) {
				return new Response($this->getAPIResultJson("E02000", "该用户名已被注册", ""));
			}

			if (!$this->checkUserName($userName)) {
				return new Response($this->getAPIResultJson("E02000", "用户名非法", ""));
			}
			$entity->setUserName($userName);
			if (!empty($email)) {
				if (!$this->checkEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "请输入正确格式的邮箱", ""));
				}
				if ($lmcu->isExistEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
				}
				$entity->setEmail($email);
			}
			if (!empty($phone)) {
				if ($lmcu->isExistPhone($phone)) {
					return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
				}
				$entity->setPhone($phone);
			}
			if (!empty($avatar)) {
				$entity->setAvatar($avatar);
			}
			$entity->setCompanyId($companyId);
			$entity->setRegisterFrom($registerFrom);
			$entity->setPassword(md5($password));
			$userId = $lmcu->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	private function checkEmail($email) {
		$pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
		if (preg_match($pattern, $email)) {
			return true;
		} else {
			return false;
		}
	}

	private function checkUserName($userName) {
		$reg = "/[ #'\"]/";
		return preg_match($reg, $userName) === 0;
	}

	public function editUserAction() {
		try
		{

			if ($this->CompanyId == -1) {
				$isAdmin = true;
			} else {
				$isAdmin = false;
			}

			$userId = intval($this->requestParameter("userId"));
			if ($userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			$lmcu = $this->get("library_model_clean_userinfo");
			$userEntity = $lmcu->getEntity($userId);
			$userInfo = new UserInfoResult();
			if ($userEntity) {
				$companyId = $userEntity->getCompanyId();
				if ($companyId == -1 || $companyId == 0) {
					$companyName = '通用';
				} else {
					$companyNameInfo = $lmcc->getEntity($companyId);
					$companyName = $companyNameInfo->getCompanyName();
				}
				$userInfo->setUserId($userEntity->getUserId());
				$userInfo->setUserName($userEntity->getUserName());
				$userInfo->setSex($userEntity->getSex());
				$userInfo->setEmail($userEntity->getEmail());
				$userInfo->setPhone(HtmlHandler::htmlDecode($userEntity->getPhone()));
				$userInfo->setAvatar($userEntity->getAvatar());
				$userInfo->setNowSn($userEntity->getNowSn());
				$userInfo->setCompanyId($userEntity->getCompanyId());
				$userInfo->setCreateTime($userEntity->getCreateTime());
				$userInfo->setCompanyName($companyName);
			}
			return $this->render("CleanAdminBundle:User:editUser.html.twig", array("companyInfo" => $companyInfo, "userInfo" => $userInfo, "isAdmin" => $isAdmin));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editUserSubmitAction() {
		try
		{

			$userId = intval($this->requestParameter("userId"));
			if ($userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			if ($this->CompanyId == -1) {
				$companyId = intval($this->requestParameter("companyId"));
			} else {
				//当前管理员
				$companyId = $this->CompanyId;
			}

			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$userName = $this->requestParameter("userName");
			$email = $this->requestParameter("email");
			$phone = $this->requestParameter("phone");
			$password = $this->requestParameter("password");
			$sex = intval($this->requestParameter("sex"));
			$avatar = $this->requestParameter("avatar");
			$lmcu = $this->get("library_model_clean_userinfo");
			$entity = $lmcu->getEntity($userId);

			if (!empty($userName)) {
				if ($entity->getUserName() != $userName) {
					if ($lmcu->isExistUserName($userName)) {
						return new Response($this->getAPIResultJson("E02000", "该用户名已被注册", ""));
					}
					if (!$this->checkUserName($userName)) {
						return new Response($this->getAPIResultJson("E02000", "用户名非法", ""));
					}
					$entity->setUserName($userName);
				}
			}

			if (!empty($email)) {
				if (!$this->checkEmail($email)) {
					return new Response($this->getAPIResultJson("E02000", "请输入正确格式的邮箱", ""));
				}
				if ($entity->getEmail() != $email) {
					if ($lmcu->isExistEmail($email)) {
						return new Response($this->getAPIResultJson("E02000", "该邮箱已被注册", ""));
					}
					$entity->setEmail($email);
				}

			}

			if (!empty($phone)) {
				if ($entity->getPhone() != $phone) {
					if ($lmcu->isExistPhone($phone)) {
						return new Response($this->getAPIResultJson("E02000", "该手机号码已被注册", ""));
					}
					$entity->setPhone($phone);
				}

			}
			if (!empty($avatar)) {
				$entity->setAvatar($avatar);
			}
			if (!empty($password)) {
				$entity->setPassword(md5(md5($password)));
			}
			if ($sex > 0) {
				$entity->setSex($sex);
			}
			$entity->setCompanyId($companyId);
			$user = $lmcu->editEntity($entity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteUserListAction() {
		try
		{
			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$userIdList = $this->requestParameter("userIdList");
			if (!$userIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $userIdList);
			$lmcu = $this->get("library_model_clean_userinfo");

			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$userInfo = $lmcu->getEntity($listArr[$i]);
					$lmcu->deleteEntity($listArr[$i]);
					//删除user_machine中数据
					$lmcum = $this->get("library_model_clean_usermachine");
					$userMachineInfo = $lmcum->getEntityByUserId($listArr[$i]);

					for ($j = 0; $j < count($userMachineInfo); $j++) {
						$lmcum->deleteEntity($userMachineInfo[$j]->getUserMachineId());
					}

					$nowSn = $userInfo->getNowSn();

					if ($nowSn && strlen($nowSn) == 16) {
						$this->deleteUserBySn($nowSn, $listArr[$i]);
					}
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//在设备中删除该用户
	private function deleteUserBySn($sn, $userId) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			//获取文件的内容
			$arr = $this->getRobot($filename);
			//修改文件的内容
			if ($arr) {
				foreach ($arr["userList"] as $key => $val) {
					if ($val["userId"] == $userId) {
						unset($arr["userList"][$key]);
					}
				}
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		}

		return true;
	}

	/*
		     * 获取用户信息文件路径
	*/
	private function getRobotFileName($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/robots/" . $subDir;

		$filename = $dir . "/" . $sn;
		if (!file_exists($filename)) {
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

	public function uploadAvatarFileAction() {

		$filePath = ConfigHandler::getCommonConfig("avatarPath");
		$fileResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, true);
		if (!is_array($fileResult)) {
			return new Response($this->getAPIResultJson("E02000", $fileResult, ""));
		}
		$filename = $fileResult["filename"];
		$url = str_replace($filePath, "", $filename);
		$avatarUrl = ConfigHandler::getCommonConfig("avatarUrl");
		$url = $avatarUrl . $url;
		$fileResult["fileName"] = $url;
		return new Response($this->getAPIResultJson("N00000", "上传成功", $fileResult));
	}

	public function getUserMachinePageListAction() {
		try
		{
			$userId = intval($this->requestParameter("userId"));

			if ($userId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");

			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			$result = $lmcum->getPageUserMachine($pageIndex, $pageSize, $userId);

			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

}
?>