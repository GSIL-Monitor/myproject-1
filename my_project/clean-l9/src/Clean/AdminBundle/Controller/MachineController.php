<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\MachineEntity;
use Clean\LibraryBundle\Entity\MachineResult;
use Clean\LibraryBundle\Entity\SystemMessageEntity;
use Clean\LibraryBundle\Entity\UserMachineEntity;
use Common\Utils\ConfigHandler;
use Symfony\Component\HttpFoundation\Response;

class MachineController extends BaseController {

	public function machinePageListAction() {
		$lmcc = $this->get("library_model_clean_company");
		if ($this->CompanyId == -1) {
			$isAdmin = true;
			$companyInfo = $lmcc->getEntityList();
		} else {
			$isAdmin = false;
			$result = $lmcc->getEntity($this->CompanyId);
			$companyInfo = array();
			$companyInfo[] = $result;
		}
		return $this->render("CleanAdminBundle:Machine:machinePageList.html.twig", array("isAdmin" => $isAdmin, "companyInfo" => $companyInfo));
	}

	public function getMachinePageListAction() {
		try
		{
			if ($this->CompanyId == -1) {
				$companyId = $this->requestParameter("companyId");
			} else {
				$companyId = $this->CompanyId;
			}

			$sn = $this->requestParameter("sn");
			$startDate = $this->requestParameter("startDate");
			$endDate = $this->requestParameter("endDate");

			//固件版本号
			$version = $this->requestParameter("version");
			//1.等于 2.大于
			$searchType = intval($this->requestParameter("searchType"));

			$lmcm = $this->get("library_model_clean_machine");
			$pageIndex = intval($this->requestParameter("pageIndex"));
			if (empty($pageIndex)) {
				$pageIndex = 1;
			}

			$pageSize = intval($this->requestParameter("pageSize"));
			if (empty($pageSize)) {
				$pageSize = 30;
			}
			$result = $lmcm->getPageMachine($pageIndex, $pageSize, $companyId, $sn, $startDate, $endDate, $version, $searchType);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addMachineAction() {
		try
		{
			$lmcc = $this->get("library_model_clean_company");
			if ($this->CompanyId == -1) {
				$isAdmin = true;
				$companyInfo = $lmcc->getEntityList();
			} else {
				$isAdmin = false;
				$result = $lmcc->getEntity($this->CompanyId);
				$companyInfo = array();
				$companyInfo[] = $result;
			}
			return $this->render("CleanAdminBundle:Machine:addMachine.html.twig", array("isAdmin" => $isAdmin, "companyInfo" => $companyInfo));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addMachineSubmitAction() {
		try
		{
			if ($this->CompanyId == -1) {
				$companyId = $this->requestParameter("companyId");
			} else {
				$companyId = $this->CompanyId;
			}
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$sn = $this->requestParameter("sn");
			$machineName = $this->requestParameter("machineName");

			if (!$sn) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcm = $this->get("library_model_clean_machine");
			$machineInfo = $lmcm->isExistSn($sn);
			if ($machineInfo) {
				return new Response($this->getAPIResultJson("E02000", "该SN已经存在，请勿重复添加", ""));
			}

			$entity = new MachineEntity();
			$entity->setSn($sn);
			$entity->setCompanyId($companyId);
			if ($machineName) {
				$entity->setMachineName($machineName);
			}
			$entity->setMachineType(1);
			$machineId = $lmcm->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editMachineAction() {
		try
		{

			$machineId = intval($this->requestParameter("machineId"));
			if ($machineId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			if ($this->CompanyId == -1) {
				$isAdmin = true;
				$companyInfo = $lmcc->getEntityList();
			} else {
				$isAdmin = false;
				$result = $lmcc->getEntity($this->CompanyId);
				$companyInfo = array();
				$companyInfo[] = $result;
			}
			$lmcm = $this->get("library_model_clean_machine");
			$machineEntity = $lmcm->getEntity($machineId);
			if (!$machineEntity) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$machineinfo = new MachineResult();
			$machineinfo->setMachineId($machineEntity->getMachineId());
			$machineinfo->setSn($machineEntity->getSn());
			$machineinfo->setMachineName($machineEntity->getMachineName());
			$machineinfo->setCompanyId($machineEntity->getCompanyId());
			$machineinfo->setCreateTime($machineEntity->getCreateTime());
			$machineinfo->setVersion($machineEntity->getVersion()); //固件版本
			$machineinfo->setHardware($machineEntity->getHardware()); //硬件版本

			$companyId = $machineEntity->getCompanyId();
			if ($companyId == -1) {
				$companyName = '通用';
			} else {
				$companyNameInfo = $lmcc->getEntity($companyId);
				$companyName = $companyNameInfo->getCompanyName();
			}
			$machineinfo->setCompanyName($companyName);
			return $this->render("CleanAdminBundle:Machine:editMachine.html.twig", array("companyInfo" => $companyInfo, "machineinfo" => $machineinfo));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editMachineSubmitAction() {
		try
		{
			if ($this->CompanyId == -1) {
				$companyId = $this->requestParameter("companyId");
			} else {
				$companyId = $this->CompanyId;
			}
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$machineId = intval($this->requestParameter("machineId"));
			$sn = $this->requestParameter("sn");
			$machineName = $this->requestParameter("machineName");

			if ($machineId <= 0 || empty($sn) || strlen($sn) != 16) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcm = $this->get("library_model_clean_machine");
			$machineEntity = $lmcm->getEntity($machineId);
			$machineEntity->setCompanyId($companyId);
			$machineEntity->setSn($sn);
			if ($machineName) {
				$machineEntity->setMachineName($machineName);
			}

			$machine = $lmcm->editEntity($machineEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteMachineListAction() {
		try
		{

			if ($this->CompanyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$machineIdList = $this->requestParameter("machineIdList");
			if (!$machineIdList) {
				return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
			}
			$listArr = explode(",", $machineIdList);
			$lmcm = $this->get("library_model_clean_machine");
			for ($i = 0; $i < count($listArr); $i++) {
				if ($listArr[$i] > 0) {
					$lmcm->deleteEntity($listArr[$i]);
				}

			}
			return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
		} catch (\Exception $ex) {

			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function getMachineUserListAction() {
		try
		{
			$companyId = $this->CompanyId;
			$sn = $this->requestParameter("sn");
			if (empty($sn)) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			$result = $lmcum->getMachineAllUser($sn);
			if ($result) {
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			} else {
				return new Response($this->getAPIResultJson("N00000", "暂无数据", ""));
			}

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//设为主人
	public function editUserUpAction() {
		try
		{
			$companyId = $this->CompanyId;
			$sn = $this->requestParameter("sn");
			$userId = intval($this->requestParameter("userId"));
			$userType = intval($this->requestParameter("userType"));
			if (empty($sn) || $userId <= 0 || $userType <= 0 || $userType != 2) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			$entity = $lmcum->isExistUserSn($sn, $userId);
			if (!$entity) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$entity->setUserType(1);
			$lmcum->editEntity($entity);
			$entityList = $lmcum->getEntityBySn($sn);
			if ($entityList) {
				for ($i = 0; $i < count($entityList); $i++) {
					if ($entityList[$i]->getUserId() != $userId && $entityList[$i]->getUserType() == 1) {
						$entityList[$i]->setUserType(2);
						$lmcum->editEntity($entityList[$i]);
					}
				}
			}
			return new Response($this->getAPIResultJson("N00000", "修改成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//解绑
	public function deleteUserMachineAction() {
		try
		{
			$companyId = $this->CompanyId;
			$sn = $this->requestParameter("sn");
			$userId = intval($this->requestParameter("userId"));
			$userType = intval($this->requestParameter("userType"));
			$userMachineId = intval($this->requestParameter("userMachineId"));
			if (empty($sn) || $userId <= 0 || $userType <= 0 || $userMachineId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "数据填写不完整", ""));
			}

			$lmcum = $this->get("library_model_clean_usermachine");
			$lmcsm = $this->get("library_model_clean_systemmessage");
			if ($userType == 1) {
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
					$fromUserId = $this->LoginUserId;
					$entity->setFromUserId($fromUserId);
					$toUserId = $entityList[$i]->getUserId();
					$entity->setToUserId($toUserId);
					$systemMessageId = $lmcsm->addEntity($entity);
				}

			} elseif ($userType == 2) {
				$lmcum->deleteEntity($userMachineId);
				//共享者
				$lmcu = $this->get("library_model_clean_userinfo");
				$userInfoEntity = $lmcu->getEntity($userId);
				if ($userInfoEntity && $userInfoEntity->getNowSn() == $sn) {
					$newSn = $lmcum->getOneEntityByUserId($userId);
					if ($newSn && $newSn->getSn()) {
						$newSn = $newSn->getSn();
					} else {
						$newSn = "";
					}
					$userInfoEntity->setNowSn($newSn);
					$lmcu->editEntity($userInfoEntity);

					//修改socket保存文件
					$this->deleteUserBySn($sn, $userId);
				}

				//添加到消息表
				$entity = new SystemMessageEntity();
				$entity->setCompanyId($companyId);
				$entity->setTitle("解绑扫地机");
				$content = "您已经解绑扫地机,SN:" . $sn;
				$entity->setMessageContent($content);
				$entity->setMessageType("1");
				$fromUserId = $this->LoginUserId;
				$entity->setFromUserId($fromUserId);
				$entity->setToUserId($userId);
				$systemMessageId = $lmcsm->addEntity($entity);
			}

			return new Response($this->getAPIResultJson("N00000", "解绑成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器错误", ""));
		}

		return new Response($this->getAPIResultJson("N00000", "修改失败", ""));
	}

	//添加
	public function addUserMachineAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$sn = $this->requestParameter("sn");
			$userName = $this->requestParameter("userName");
			$phone = $this->requestParameter("phone");
			$email = $this->requestParameter("mail");
			$userId = intval($this->requestParameter("userId"));
			$userType = intval($this->requestParameter("userType"));
			$noteName = $this->requestParameter("noteName");
			if (empty($sn) || $userType <= 0) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			// if(empty($userName) && $userId<=0)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			// }
			//判断用户名
			if ($userId <= 0) {
				$lmcu = $this->get("library_model_clean_userinfo");
				if ($userName) {
					$userInfoEntity = $lmcu->isExistUserName($userName);
				} elseif ($phone) {
					//$phone = "+86&nbsp;".$phone;
					$userInfoEntity = $lmcu->isExistPhone($phone);

				} elseif ($email) {
					$userInfoEntity = $lmcu->isExistEmail($email);

				} else {
					return new Response($this->getAPIResultJson("E02000", "找不到该用户", ""));
				}

				if (!$userInfoEntity || $userInfoEntity->getUserId() <= 0) {
					return new Response($this->getAPIResultJson("E02000", "找不到该用户", ""));
				}
				$userId = $userInfoEntity->getUserId();
			}

			//判断SN是否存在
			$lmcm = $this->get("library_model_clean_machine");
			$entity = $lmcm->isExistSn($sn);
			if (!$entity) {
				return new Response($this->getAPIResultJson("E02000", "该SN不存在", ""));
			}
			$lmcum = $this->get("library_model_clean_usermachine");
			$userMachine = $lmcum->getEntityByUserIdAndSn($userId, $sn);
			if ($userMachine) {
				return new Response($this->getAPIResultJson("E02000", "数据已存在，请勿重复添加", ""));
			}

			$ower = $lmcum->getOwerBySn($sn);

			if ($userType == 1) {
				if ($ower) {
					return new Response($this->getAPIResultJson("E02000", "该设备已经存在主人，请先解绑", ""));
				}
			} else {
				//判断是否有主人，如果没有主人，则不能添加共享者

				if (!$ower) {
					return new Response($this->getAPIResultJson("E02000", "请先添加主人", ""));
				}
			}

			$userMachineEntity = new UserMachineEntity();
			$userMachineEntity->setUserId($userId);
			$userMachineEntity->setSn($sn);
			$userMachineEntity->setUserType($userType);
			if (!empty($noteName)) {
				$userMachineEntity->setNoteName($noteName);
			}
			$lmcum->addEntity($userMachineEntity);

			$lmcu = $this->get("library_model_clean_userinfo");
			$userInfoEntity = $lmcu->getEntity($userId);
			if (!$userInfoEntity->getNowSn()) {
				$userInfoEntity->setNowSn($sn);
				$lmcu->editEntity($userInfoEntity);
				//修改socket保存文件
				$filename = $this->getRobotFileName($sn);
				if ($filename) {
					$robotUserList = $lmcum->getUserRobotListBySN($sn);
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
			}

			//添加到消息表
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$entity = new SystemMessageEntity();
			$entity->setCompanyId($companyId);
			$entity->setTitle("绑定扫地机");
			$content = "您已经绑定扫地机,SN:" . $sn;
			$entity->setMessageContent($content);
			$entity->setMessageType("1");
			$fromUserId = $this->LoginUserId;
			$entity->setFromUserId($fromUserId);
			$entity->setToUserId($userId);
			$systemMessageId = $lmcsm->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//查看是否有用户或者扫地机在线
	public function getUserOrMachineIsOnlineAction() {
		try
		{
			$companyId = $this->CompanyId;
			if ($companyId <= 0 && $companyId != -1) {
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}

			$type = intval($this->requestParameter("type")); //1:sn 2:user
			if (!$type) {
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$isOnline = 0;

			if ($type == 1) {
				$sn = $this->requestParameter("sn");
				if (!$sn) {
					return new Response($this->getAPIResultJson("E02000", "请填写sn号", ""));
				}
				$lmcm = $this->get("library_model_clean_machine");
				$entity = $lmcm->isExistSn($sn);
				if (!$entity) {
					return new Response($this->getAPIResultJson("E02000", "该SN不存在", ""));
				}

				$isOnline = $this->getRobotIsOnline($sn) ? 1 : 0;
			} elseif ($type == 2) {

				$userName = $this->requestParameter("userName");
				$lmcu = $this->get("library_model_clean_userinfo");
				$userInfoEntity = $lmcu->getUserInfoByLoginName($userName);

				if (!$userInfoEntity || $userInfoEntity->getUserId() <= 0) {
					return new Response($this->getAPIResultJson("E02000", "找不到该用户", ""));
				}
				$userId = $userInfoEntity->getUserId();
				$isOnline = $this->getUserIsOnline($userId) ? 1 : 0;
			}

			return new Response($this->getAPIResultJson("N00000", "获取成功", $isOnline));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	//在设备中添加该用户，重新获取
	private function addUserBySn($sn) {
		$filename = $this->getRobotFileName($sn);
		if ($filename) {
			$lmcum = $this->get("library_model_clean_usermachine");
			$robotUserList = $lmcum->getUserRobotListBySN($sn);
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
		return true;
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

	/*
		     * 获取机器是否在线
	*/
	private function getRobotIsOnline($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/robots/" . $subDir;

		$filename = $dir . "/" . $sn;
		if (!file_exists($filename)) {
			return false;
		}
		$content = $this->getRobot($filename);
		$fd = intval($content["fd"]);
		if (!$content || !$fd) {
			//unlink($filename);
			return false;
		}

		$fdSubDir = substr(strval($fd), 0, 1);
		$fdFilename = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/fd/" . $fdSubDir . "/" . $fd;

		if (!file_exists($fdFilename)) {
			//unlink($filename);
			return false;
		}

		return $filename;
	}

	/*
		     * 获取用户是否在线
	*/
	private function getUserIsOnline($userId) {
		$subDir = substr($userId, 0, 1);
		$dir = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/users/" . $subDir;

		$filename = $dir . "/" . $userId;
		if (!file_exists($filename)) {
			return false;
		}
		$content = $this->getRobot($filename);
		$fd = intval($content["fd"]);
		if (!$content || !$fd) {
			//unlink($filename);
			return false;
		}

		$fdSubDir = substr(strval($fd), 0, 1);
		$fdFilename = ConfigHandler::getCommonConfig("CONNECTOR_DIR") . "/fd/" . $fdSubDir . "/" . $fd;

		if (!file_exists($fdFilename)) {
			//unlink($filename);
			return false;
		}

		return $filename;
	}

}
?>