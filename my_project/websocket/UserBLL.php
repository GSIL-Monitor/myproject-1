<?php
require_once 'SweepingDatabaseHelper.php';
require_once dirname(dirname(__FILE__)) . '/common/FileHandler.php';
require_once dirname(dirname(__FILE__)) . '/common/AESCryptHandler.php';
require_once 'CommonDefine.php';

class UserBLL {
	/*
		     * 清理数据
	*/
	public function clearData() {
		FileHandler::deleteFiles("/" . trim(CommonDefine::CONNECTOR_DIR, "/"));
	}

	/*
		     * 获取最后检查的fd
	*/
	public function getLastCheckFd() {
		$filename = $this->getCheckFdFileName();
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			return intval($content);
		}
		return 0;
	}

	/*
		     * 写入最后检查的fd
	*/
	public function writeLastCheckFd($fd) {
		$filename = $this->getCheckFdFileName();
		$fp = fopen($filename, "w");
		fwrite($fp, strval($fd));
		fclose($fp);
	}

	/*
		     * 获取检查记录文件位置
	*/
	private function getCheckFdFileName() {
		$dir = CommonDefine::CONNECTOR_DIR . "chk";
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		$filename = $dir . "/fd";
		return $filename;
	}

	/*
		     * 获取fd内容
	*/
	public function getConnector($fd) {
		$filename = $this->getConnectorFileName($fd);
		if (!file_exists($filename)) {
			return null;
		}
		$content = file_get_contents($filename);
		return json_decode($content, true);
	}

	/*
		     * 获取该连接的用户Id
	*/
	private function getConnectorId($fd) {
		$connector = $this->getConnector($fd);
		if (!empty($connector)) {
			if (array_key_exists("deviceType", $connector)) {
				$deviceType = intval($connector["deviceType"]);
				if ($deviceType == CommonDefine::DEVICE_TYPE_APP) {
					if (array_key_exists("userId", $connector)) {
						if ($connector["userId"] <= 0) {
							return false;
						}
						//获取用户文件
						$filename = $this->getUserFileName($connector["userId"]);
						if (!file_exists($filename)) {
							return false;
						} else {
							$content = file_get_contents($filename);
							$userInfo = json_decode($content, true);
							if ($userInfo["fd"] != $fd) {
								return false;
							}
						}
						return intval($connector["userId"]);
					}
				} else {
					if (array_key_exists("sn", $connector)) {
						if (strlen($connector["sn"]) < 16) {
							return false;
						}
						$filename = $this->getRobotFileName($connector["sn"]);
						if (!file_exists($filename)) {
							return false;
						} else {
							$content = file_get_contents($filename);
							$robotInfo = json_decode($content, true);
							$robotInfoFd = isset($robotInfo["fd"]) ? $robotInfo["fd"] : 0;
							if ($robotInfoFd != $fd) {
								return false;
							}
						}
						return $connector["sn"];
					}
				}

			}
		}
		return 0;
	}
	/*
		     * 获取连接类型
	*/
	public function getConnectionType($fd) {
		$connector = $this->getConnector($fd);
		if (!empty($connector) && array_key_exists("connectionType", $connector)) {
			return intval($connector["connectionType"]);
		}
		return CommonDefine::CONNECTION_TYPE_SOCKET;
	}

	/*
		     * 获取设备类型
	*/
	public function getDeviceType($fd) {
		$connector = $this->getConnector($fd);
		if (!empty($connector) && array_key_exists("deviceType", $connector)) {
			return intval($connector["deviceType"]);
		}
		return 0;
	}

	/*
		     * 是否合法连接
	*/
	public function isValidConnector($fd) {
		$userId = $this->getConnectorId($fd);
		return $userId;
	}

	/*
		     * 是否连接超时 目前只用于连接但未进行验证时断开处理
	*/
	public function isConnectorTimeOut($fd) {
		$connector = $this->getConnector($fd);
		if (!empty($connector)) {
			if (array_key_exists("userId", $connector) && intval($connector["userId"]) > 0) {
				return false;
			} else if (array_key_exists("sn", $connector) && !empty($connector["sn"])) {
				return false;
			}
			$createTime = $connector["createTime"];
			if ($createTime > strtotime("-5 seconds")) {
				return false;
			}

		}
		return true;
	}

	/*
		     * 保存连接到连接池
	*/
	public function saveConnector($fd, $connectionType = 0, $id = 0, $deviceType = 0) {
		$filename = $this->getConnectorFileName($fd);
		$arr = array();
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$arr = json_decode($content, true);
		}

		if (!array_key_exists("connectionType", $arr) && $connectionType > 0) {
			$arr["connectionType"] = $connectionType;
		}
		if (!array_key_exists("createTime", $arr)) {
			$arr["createTime"] = time();
		}

		$userFd = 0;
		//保存手机用户信息
		if ($deviceType == CommonDefine::DEVICE_TYPE_APP) {

			$userId = intval($id);
			if ($userId > 0) {
				//先获取用户信息
				$filename = $this->getUserFileName($userId);
				if (file_exists($filename)) {
					$content = file_get_contents($filename);
					$result = json_decode($content, true);
					$userFd = $result["fd"];
				}
				//保存用户信息
				$flag = $this->saveUser($fd, $userId, $connectionType);
				if ($flag) {
					$arr["userId"] = $userId;
					$arr["deviceType"] = CommonDefine::DEVICE_TYPE_APP;
				}
			}
		}
		//保存机器人信息
		else {
			$sn = $id;
			if (!empty($sn)) {
				//保存机器人信息
				$flag = $this->saveRobot($fd, $sn, $connectionType);
				if ($flag) {
					$arr["sn"] = $sn;
					$arr["deviceType"] = CommonDefine::DEVICE_TYPE_ROBOT;
				}
			}
		}

		//保存连接
		$filename = $this->getConnectorFileName($fd);
		$fp = fopen($filename, "w");
		fwrite($fp, json_encode($arr));
		fclose($fp);

		return $userFd;
	}

	/*
		     * 将连接从连接池中移除
	*/
	public function removeConnector($fd) {
		$deviceInfo = $this->getConnector($fd);
		if (!empty($deviceInfo)) {
			if (array_key_exists("deviceType", $deviceInfo)) {
				$deviceType = intval($deviceInfo["deviceType"]);
				if ($deviceType == CommonDefine::DEVICE_TYPE_APP) {
					//移除用户信息
					$userId = $deviceInfo["userId"];
					$this->removeUser($userId, $fd);
				} else {
					//移除机器信息
					$sn = $deviceInfo["sn"];
					$this->removeRobot($sn);
				}

			}
		}
		//移除连接池
		$filename = $this->getConnectorFileName($fd);
		if (file_exists($filename)) {
			unlink($filename);
		}

	}

	public function getToFdInfo($fd, $dataArr) {
		$result = array(
			"fd" => 0,
			"connectionType" => 0,
		);
		$res = array();
		$deviceType = $this->getDeviceType($fd);
		if ($deviceType == CommonDefine::DEVICE_TYPE_APP) {
			if (!array_key_exists("sn", $dataArr)) {
				return $result;
			}
			$sn = $dataArr["sn"];
			$robotInfo = $this->getRobot($sn);
			if (!empty($robotInfo) && array_key_exists("fd", $robotInfo)) {
				$res[] = $robotInfo;
			}
		} else if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT) {
			//根据fd找到sn
			$robotInfo = $this->getRobotByFd($fd);
			$userInfoArr = $robotInfo["userList"];
			for ($i = 0; $i < count($userInfoArr); $i++) {
				$userId = $userInfoArr[$i]["userId"];
				$userInfo = $this->getUser($userId);
				if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
					$res[] = $userInfo;
				}
			}
		}
		return $res;
	}

	public function getUserFdInfo($userId, $sn)
    {
        if ($userId > 0) {
            $userInfo = $this->getUser($userId);
            if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
                $res[] = $userInfo;
            }
        } else {
            if (!$sn) {
                return array();
            }
            $robotInfo = $this->getRobot($sn);
            if (!$robotInfo) {
                return array();
            }
            $userInfoArr = isset($robotInfo["userList"]) ? $robotInfo["userList"] : array();
            if (!$userInfoArr) {
                return array();
            }

            for ($i = 0; $i < count($userInfoArr); $i++) {
                $userId = $userInfoArr[$i]["userId"];
                $userInfo = $this->getUser($userId);
                if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
                    $res[] = $userInfo;
                }
            }
        }
        return $res;
    }

	//获取单对单或者所有
	public function getDefaultToFdInfo($fd, $dataArr, $deviceType = 0, $extendArr = array()) {
		// $result=array(
		//     "fd"=>0,
		//     "connectionType"=>0
		// );
		$res = array();
		if (!$deviceType) {
			$deviceType = $this->getDeviceType($fd);
		}

		if ($deviceType == CommonDefine::DEVICE_TYPE_APP) {
			if (!array_key_exists("sn", $dataArr)) {
				return $res;
			}
			$sn = $dataArr["sn"];
			$robotInfo = $this->getRobot($sn);

			if (!empty($robotInfo) && array_key_exists("fd", $robotInfo)) {
				//判断用户是否还在sn列表
				if (!$this->checkMachineUser($sn, 0, $fd)) {
					return $res;
				}
				$res[] = $robotInfo;
			}
		} else if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT) {
			$userId = 0;
			if (array_key_exists("userId", $dataArr)) {
				$userId = intval($dataArr["userId"]);
			}
			if ($userId > 0) {
				$userInfo = $this->getUser($userId);
				$res[] = $userInfo;
			} else {
				$robotInfo = $this->getRobotByFd($fd);
				$userInfoArr = $robotInfo["userList"];
				for ($i = 0; $i < count($userInfoArr); $i++) {
					$userId = $userInfoArr[$i]["userId"];
					$userInfo = $this->getUser($userId);
					if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
						$res[] = $userInfo;
					}
				}
			}
		} else if ($deviceType == CommonDefine::DEVICE_TYPE_ROBOT_HTTPS) {
			$userId = 0;
			if (array_key_exists("userId", $extendArr)) {
				$userId = intval($extendArr["userId"]);
			}

			if ($userId > 0) {
				$userInfo = $this->getUser($userId);
				if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
					$res[] = $userInfo;
				}
			} else {
				if (!isset($dataArr["sn"]) || !$dataArr["sn"]) {
					return array();
				}
				$robotInfo = $this->getRobot($dataArr["sn"]);
				if (!$robotInfo) {
					return array();
				}
				$userInfoArr = isset($robotInfo["userList"]) ? $robotInfo["userList"] : array();
				if (!$userInfoArr) {
					return array();
				}

				for ($i = 0; $i < count($userInfoArr); $i++) {
					$userId = $userInfoArr[$i]["userId"];
					$userInfo = $this->getUser($userId);
					if (!empty($userInfo) && array_key_exists("fd", $userInfo) && (!isset($userInfo["isLocalNetwork"]) || intval($userInfo["isLocalNetwork"]) == 0)) {
						$res[] = $userInfo;
					}
				}
			}

		}
		return $res;
	}

	private function checkMachineUser($sn = '', $userId = 0, $fd = 0) {
		$robotInfo = $this->getRobot($sn);

		if (!$userId) {
			$userInfo = $this->getUserByFd($fd);
			if (!$userInfo) {
				return false;
			}
			$userId = $userInfo["userId"];
		}

		if ($robotInfo) {
			$userList = isset($robotInfo["userList"]) ? $robotInfo["userList"] : array();
			for ($i = 0; $i < count($userList); $i++) {
				$mUserId = $userList[$i]["userId"];
				if ($mUserId == $userId) {
					return true;
				}
			}
		}

		return false;
	}

	//更新用户状态（是否连上局域网）
	public function updateUserInfo($fd, $dataArr) {
		$userId = intval($dataArr["userId"]);
		if (!$userId) {
			return false;
		}
		$filename = $this->getUserFileName($userId);
		$userInfo = $this->getUser($userId);
		if ($userInfo) {
			$userInfo["fd"] = $fd;
			$userInfo["isLocalNetwork"] = intval($dataArr["isLocalNetwork"]);
		}
		$fp = fopen($filename, "w");
		fwrite($fp, json_encode($userInfo));
		fclose($fp);
		return ture;
	}

	public function bindMachine($fd, $dataArr) {
		$result = array(
			"fd" => 0,
			"connectionType" => 0,
		);
		if (array_key_exists("bindId", $dataArr)) {
			$userId = intval($dataArr["bindId"]);
		}
		if (!$userId || $userId <= 0) {
			$dataArr['res'] = 0;
		}
		$sn = $this->getConnectorId($fd);
		if ($userId && $sn) {
			//绑定,并且设为主人
			$paramArr = array(
				"userId" => $userId,
				"sn" => $sn,
				"userType" => 1,
			);
			$dbObj = new SweepingDatabaseHelper();
			$IsExist = $dbObj->getUserMachinetBySNAndUserId($sn, $userId);
			if (empty($IsExist)) {
				$userMachineId = $dbObj->addUserMachine($paramArr);
			} else {
				$dataArr['res'] = 1;
			}
			//将其他人降为普通用户
			$res = $dbObj->updateUserMachine($sn, $userId);
			//如果原来设备存在订阅sn,则先删除设备中该用户
			$userInfo = $dbObj->getUserNowSn($userId);
			if ($userInfo["nowSn"] && strlen($userInfo["nowSn"]) == 16) {
				$nowSnInfo = $dbObj->getRobotInfo($userInfo["nowSn"]);
				//修改文件的内容
				if ($nowSnInfo) {
					$nowSnFilename = $this->getRobotFileName($userInfo["nowSn"]);

					for ($i = 0; $i < count($nowSnInfo["userList"]); $i++) {
						if ($nowSnInfo["userList"][$i]["userId"] == $userId) {
							unset($nowSnInfo["userList"][$i]);
							break;
						}
					}

					$fp = fopen($nowSnFilename, "w");
					fwrite($fp, json_encode($nowSnInfo));
					fclose($fp);
				}
			}
			//修改该用户的订阅SN
			$dbObj->updateUserInfo($sn, $userId);
			//将用户添加到机器用户信息
			$robotInfo = $dbObj->getRobotInfo($sn);
			$filename = $this->getRobotFileName($sn);
			$arr = $this->getRobot($sn);
			//修改文件的内容
			if ($arr) {
				$arr["userList"] = $robotInfo["userList"];
				$fp = fopen($filename, "w");
				fwrite($fp, json_encode($arr));
				fclose($fp);
			}
		} else {
			$dataArr['res'] = 0;
		}
		$userInfo = $this->getUser($userId);
		if (!empty($userInfo) && array_key_exists("fd", $userInfo)) {
			$result = $userInfo;
		}
		$res = array("result" => $result, "dataArr" => $dataArr);
		return $res;
	}

	public function checkStatus($fd, $dataArr) {
		$result = array();
		if (array_key_exists("sn", $dataArr)) {
			//APP查看设备是否在线
			//type 0:不在线 1：在线  2：被解绑
			$sn = $dataArr["sn"];
			if (strlen($sn) < 16) {
				$result["isExistConnect"] = false;
				$result["type"] = 0;
				return $result;
			}

			//判断用户是否还在sn列表
			if (!$this->checkMachineUser($sn, $dataArr["userId"], 0)) {
				$result["isExistConnect"] = false;
				$result["type"] = 2;
				return $result;
			}

			$subDir = substr($sn, 0, 2);
			$dir = CommonDefine::CONNECTOR_DIR . "robots/" . $subDir . "/" . $sn;
			if (!file_exists($dir)) {
				$result["isExistConnect"] = false;
				$result["type"] = 0;

			} else {
				$result["isExistConnect"] = true;
				$result["type"] = 1;
			}
		} else {
			//设备查看是否有用户在线
			$res = $this->getRobotByFd($fd);
			if ($res) {
				$userList = isset($res["userList"]) ? $res["userList"] : array();
				for ($i = 0; $i < count($userList); $i++) {
					$userId = $userList[$i]["userId"];
					$subDir = substr(strval($userId), 0, 1);
					$dir = CommonDefine::CONNECTOR_DIR . "users/" . $subDir . "/" . $userId;
					if (file_exists($dir)) {
						//判断是否存在且连接是否存在
						$content = file_get_contents($dir);
						$info = json_decode($content, true);
						$fd = $info["fd"];
						$temDir = $this->getConnectorFileName($fd);
						if (file_exists($temDir)) {
							$result["isExistConnect"] = true;
							return $result;
						} else {
							unlink($dir);
						}
					}
				}
				$result["isExistConnect"] = false;
			} else {
				$result["isExistConnect"] = false;
			}
		}

		return $result;
	}

	/*
		     * 获取连接在连接池中的位置
		    * type 1为fd 2为user
	*/
	private function getConnectorFileName($fd) {
		$fd = intval($fd);
		$subDir = substr(strval($fd), 0, 1);
		$dir = CommonDefine::CONNECTOR_DIR . "fd/" . $subDir;
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$filename = $dir . "/" . $fd;
		return $filename;
	}

	/*
		     * 获取用户信息
	*/
	private function getUser($userId) {
		$filename = $this->getUserFileName($userId);
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$result = json_decode($content, true);
			if (!empty($result)) {
				$result["userName"] = base64_decode($result["userName"]);
			}
			return $result;
		}
		return null;
	}

	/*
		     * 获取用户信息
	*/
	private function getUserByFd($fd) {
		$userId = $this->getConnectorId($fd);
		return $this->getUser($userId);
	}

	/*
		     * 添加用户连接信息
	*/
	private function saveUser($fd, $userId, $connectionType) {
		if (!$fd || !$userId || !$connectionType) {
			return false;
		}
		$dbObj = new SweepingDatabaseHelper();

		$userInfo = $dbObj->getUserInfo($userId);
		if (empty($userInfo)) {
			echo "user not exist";
			return false;
		}

		$arr = array();
		$arr["userId"] = $userId;
		$arr["fd"] = $fd;
		$arr["userName"] = base64_encode($userInfo["userName"]);
		$arr["avatar"] = $userInfo["avatar"];
		$arr["connectionType"] = $connectionType;
		$arr["robotList"] = $userInfo["robotList"];

		$filename = $this->getUserFileName($userId);
		$fp = fopen($filename, "w");
		fwrite($fp, json_encode($arr));
		fclose($fp);
		return true;
	}

	/*
		     * 删除用户连接信息
	*/
	private function removeUser($userId, $fd) {
		$filename = $this->getUserFileName($userId);
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$result = json_decode($content, true);
			if ($result["fd"] == $fd) {
				unlink($filename);
			}
		}
	}

	/*
		     * 获取用户信息文件路径
	*/
	private function getUserFileName($userId) {
		$subDir = substr(strval($userId), 0, 1);
		$dir = CommonDefine::CONNECTOR_DIR . "users/" . $subDir;
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$filename = $dir . "/" . $userId;
		return $filename;
	}

	//---------------robot------------------

	/*
		     * 获取扫地机信息
	*/
	public function getRobot($sn) {
		$filename = $this->getRobotFileName($sn);
		if (file_exists($filename)) {
			$content = file_get_contents($filename);
			$result = json_decode($content, true);
			return $result;
		}
		return null;
	}

	/*
		     * 获取用户信息
	*/
	private function getRobotByFd($fd) {
		$sn = $this->getConnectorId($fd);
		return $this->getRobot($sn);
	}

	/*
		     * 添加用户连接信息
	*/
	private function saveRobot($fd, $sn, $connectionType) {
		if (!$fd || !$sn || !$connectionType) {
			return false;
		}
		$dbObj = new SweepingDatabaseHelper();
		$robotInfo = $dbObj->getRobotInfo($sn);

		if (empty($robotInfo)) {
			echo "robot not exist";
			return false;
		}

		$arr = array();
		$arr["sn"] = $sn;
		$arr["fd"] = $fd;
		//$arr["machineName"]= $robotInfo["machineName"];
		$arr["connectionType"] = $connectionType;
		$arr["userList"] = $robotInfo["userList"];

		$filename = $this->getRobotFileName($sn);
		$fp = fopen($filename, "w");
		fwrite($fp, json_encode($arr));
		fclose($fp);
		chmod($filename, 0777);
		return true;
	}

	/*
		     * 删除用户连接信息
	*/
	private function removeRobot($sn) {
		$filename = $this->getRobotFileName($sn);
		if (file_exists($filename)) {
			unlink($filename);
		}
	}

	/*
		     * 获取用户信息文件路径
	*/
	private function getRobotFileName($sn) {
		$subDir = substr($sn, 0, 2);
		$dir = CommonDefine::CONNECTOR_DIR . "robots/" . $subDir;
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$filename = $dir . "/" . $sn;
		return $filename;
	}

	//---------------robot------------------

	/*
		     * 验证用户 包括加密解密验证、token验证、时效性验证
	*/
	public function validateUser($token) {
		return true;
		if (!empty($token)) {
			$token = AESCryptHandler::decrypt($token);

			if (strstr($token, "@")) {
				$arr = explode("@", $token);

				if (!$this->checkTimeValidate($arr[1])) {
					return false;
				}
				return true;
			}
		}
		return false;
	}

	/*
		     * 验证用户 包括加密解密验证、token验证、时效性验证
	*/
	public function validateLoginUserNew($userId, $token) {
		if (!empty($token)) {
			$dbObj = new SweepingDatabaseHelper();
			$loginUserInfo = $dbObj->getUserLoginToken($userId);
			if (empty($loginUserInfo)) {
				return 1;
			}

			$token = AESCryptHandler::decrypt($token, $loginUserInfo["aesKEY"], $loginUserInfo["aesIV"]);
			if (strstr($token, CommonDefine::SPLIT_WORD)) {
				$arr = explode(CommonDefine::SPLIT_WORD, $token);
				$token = $arr[0];
				if ($token != $loginUserInfo["loginToken"]) {
					//token过期
					return 2;
				}

				if (!$this->checkTimeValidate($arr[1])) {

					return 1;
				}
				return 0;
			}
		}
		return 1;
	}

	/*
		     * tokenTime 时间戳
	*/
	private function checkTimeValidate($tokenTime) {
		return true;
		$tempTime = intval($tokenTime);
		if (empty($tempTime)) {
			return false;
		}
		$submitTime = strtotime("+3 minute", $tempTime);

		$nowTime = time();
		if ($submitTime < $nowTime) {
			return false;
		}
		return true;
	}

}

?>