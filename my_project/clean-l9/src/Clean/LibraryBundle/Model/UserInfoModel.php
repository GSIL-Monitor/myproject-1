<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\UserInfoResult;
use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Model\Common\Paginator;

class UserInfoModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:UserInfoEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"userId" => $id,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getEntityByAuthenticationToken($authenticationToken) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"authenticationToken" => $authenticationToken,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getUserInfoByLoginName($loginName) {
		if (empty($loginName)) {
			return null;
		}

		$query = $this->getResponsity()->createQueryBuilder("u")
			->where("(lower(u.userName)=lower(:loginName) or lower(u.email)=lower(:loginName) or lower(u.phone)=lower(:loginName))  and u.status=:status")
			->setParameters(array(
				"loginName" => $loginName,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			))
			->setMaxResults(1)
			->getQuery();
		$data = $query->getResult();
		if (!empty($data)) {
			return $data[0];
		} else {
			return null;
		}
	}

	//删除所有订阅sn
	public function deleteNowSnBySn($sn) {
		$entityList = $this->getResponsity()->findBy(
			array(
				"nowSn" => $sn,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);

		if ($entityList) {
			for ($i = 0; $i < count($entityList); $i++) {

				$entityList[$i]->setNowSn("");
				$this->editEntity($entityList[$i]);
			}

		}

		return true;
	}

	public function getUserInfoByLogin($loginName, $password) {
		if (empty($loginName) || empty($password)) {
			return null;
		}

		$query = $this->getResponsity()->createQueryBuilder("u")
			->where("(lower(u.userName)=lower(:loginName) or lower(u.email)=lower(:loginName) or lower(u.phone)=lower(:loginName)) and u.password=:password and u.status=:status")
			->setParameters(array(
				"loginName" => $loginName,
				"password" => $password,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			))
			->setMaxResults(1)
			->getQuery();
		$data = $query->getResult();
		if (!empty($data)) {
			return $data[0];
		} else {
			return null;
		}
	}

	public function getPageUser($pageIndex, $pageSize, $companyId, $searchType, $keyword, $startDate, $endDate, $sn) {

		$whereStr = "ui.status = :status";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);

		if ($companyId > 0) {
			$whereStr .= " and ui.companyId=:companyId";
			$paramArr["companyId"] = $companyId;
		}

		if ($searchType > 0 && !empty($keyword)) {
			if ($searchType == "1") {
				$whereStr .= " and ui.phone=:keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "2") {
				$whereStr .= " and ui.email = :keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "3") {
				$whereStr .= " and ui.userName = :keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "4") {
				$whereStr .= " and ui.userId = :keyword";
				$paramArr["keyword"] = $keyword;
			}
		}
		if (!empty($startDate)) {
			$whereStr .= " and ui.createTime>=:startDate";
			$paramArr["startDate"] = $startDate;
		}
		if (!empty($endDate)) {
			$whereStr .= " and ui.createTime<=:endDate";
			$paramArr["endDate"] = $endDate;
		}

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('ui')
			->from('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui')
			->setParameters($paramArr)
			->where($whereStr)
			->orderBy('ui.userId', 'DESC');

		try
		{
			$page = new Paginator();
			$data = $page->paginate($query, $pageIndex, $pageSize);
			$result = new PageResult();
			$result->data = $data;
			$result->pageIndex = $pageIndex;
			$result->pageSize = $pageSize;
			$result->totalCount = $page->getCount();
			$result->totalPages = $page->getTotalPages();
			return $result;
		} catch (NoResultException $e) {
		}

		return null;

	}

	public function getPageUserAndCompany($pageIndex, $pageSize, $companyId, $searchType, $keyword, $startDate, $endDate, $sn, $userAppVersion) {

		$whereStr = "ui.status = :status";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);

		if ($searchType > 0 && !empty($keyword)) {
			if ($searchType == "1") {
				$whereStr .= " and ui.phone=:keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "2") {
				$whereStr .= " and ui.email = :keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "3") {
				$whereStr .= " and ui.userName = :keyword";
				$paramArr["keyword"] = $keyword;
			} else if ($searchType == "4") {
				$whereStr .= " and ui.userId = :keyword";
				$paramArr["keyword"] = $keyword;
			}
		}
		if (!empty($startDate)) {
			$whereStr .= " and ui.createTime>=:startDate";
			$paramArr["startDate"] = $startDate;
		}
		if (!empty($endDate)) {
			$whereStr .= " and ui.createTime<=:endDate";
			$paramArr["endDate"] = $endDate;
		}

		if ($companyId > 0 || $companyId == -1) {
			$whereStr .= " and ui.companyId=:companyId";
			$paramArr["companyId"] = $companyId;
		}

		if ($userAppVersion) {
			$whereStr .= " and ui.userAppVersion=:userAppVersion";
			$paramArr["userAppVersion"] = $userAppVersion;
		}

		$umWhereStr = " and um.status=:status";

		$mWhereStr = " and c.status=:status";

		$query = $this->entityManager->createQueryBuilder();
		if (!empty($sn)) {
			$whereStr .= " and um.sn=:sn ";
			$paramArr["sn"] = $sn;

			$query
				->select('ui,c.companyName')
				->from('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui')
				->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
					\Doctrine\ORM\Query\Expr\Join::WITH,
					'ui.companyId = c.companyId' . $mWhereStr
				)
				->leftJoin('Clean\LibraryBundle\Entity\UserMachineEntity', 'um',
					\Doctrine\ORM\Query\Expr\Join::WITH,
					'ui.userId = um.userId' . $umWhereStr
				)
				->setParameters($paramArr)
				->where($whereStr)
				->orderBy('ui.userId', 'DESC');
		} else {
			$query
				->select('ui,c.companyName')
				->from('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui')
				->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
					\Doctrine\ORM\Query\Expr\Join::WITH,
					'ui.companyId = c.companyId' . $mWhereStr
				)
				->setParameters($paramArr)
				->where($whereStr)
				->orderBy('ui.userId', 'DESC');
		}

		try
		{
			$page = new Paginator();
			$data = $page->paginate($query, $pageIndex, $pageSize);
			$dataResult = array();
			if (!empty($data)) {
				for ($i = 0; $i < count($data); $i++) {
					$tempResult = new UserInfoResult();
					$tempResult->setUserId($data[$i][0]->getUserId());
					$tempResult->setUserName($data[$i][0]->getUserName());
					$tempResult->setNickName($data[$i][0]->getNickName());
					$tempResult->setEmail($data[$i][0]->getEmail());
					$tempResult->setPhone($data[$i][0]->getPhone());
					$tempResult->setSex($data[$i][0]->getSex());
					$tempResult->setCompanyId($data[$i][0]->getCompanyId());
					$tempResult->setNowSn($data[$i][0]->getNowSn());
					$tempResult->setAvatar($data[$i][0]->getAvatar());
					$tempResult->setUserAppVersion($data[$i][0]->getUserAppVersion());
					$tempResult->setCreateTime($data[$i][0]->getCreateTime());
					if (!$data[$i]['companyName']) {
						$companyName = "通用";
					} else {
						$companyName = $data[$i]['companyName'];
					}
					$tempResult->setCompanyName($companyName);
					array_push($dataResult, $tempResult);
				}
			}
			$result = new PageResult();
			$result->data = $dataResult;
			$result->pageIndex = $pageIndex;
			$result->pageSize = $pageSize;
			$result->totalCount = $page->getCount();
			$result->totalPages = $page->getTotalPages();
			return $result;
		} catch (NoResultException $e) {
		}

		return null;

	}

	public function getUserInfoByEmailAndPassword($email, $password) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"email" => $email,
			"password" => $password,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $userInfo;
	}

	public function getUserInfoByPhoneAndPassword($phone, $password) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"phone" => $phone,
			"password" => $password,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $userInfo;
	}

	public function isExistEmail($email) {
		$userInfo = $this->getUserInfoByEmail($email);
		if ($userInfo) {
			return $userInfo;
		}
		return false;
	}

	public function getUserInfoByEmail($email) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"email" => $email,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $userInfo;
	}

	public function getUserInfoByOpenId($openId) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"openId" => $openId,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $userInfo;
	}

	public function getUserInfoByFacebookId($facebookId) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"facebookId" => $facebookId,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $userInfo;
	}

	public function isExistUserName($userName) {
		$userInfo = $this->getUserInfoByUserName($userName);

		if ($userInfo) {
			return $userInfo;
		}
		return false;
	}

	public function getUserInfoByUserName($userName) {
		$userInfo = $this->getResponsity()->findOneBy(array(
			"userName" => $userName,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));

		return $userInfo;
	}

	public function isExistPhone($phone) {
		$entity = $this->getUserInfoByPhone($phone);
		if (!empty($entity)) {
			return $entity;
		}
		return false;
	}

	public function getUserInfoByPhone($phone) {
		$entity = $this->getResponsity()->findOneBy(array(
			"phone" => $phone,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $entity;
	}

	public function getUserInfoByDingDongUserId($dingDongUserId)
    {
        $userInfo = $this->getResponsity()->findOneBy([
            'dingDongUserId' => $dingDongUserId,
            "status" => CommonDefine::DATA_STATUS_NORMAL,
        ]);
        return $userInfo;
    }

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addEntity()
	*/
	public function addEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}
		$entity->setLastLoginTime(new \DateTime());
		$entity->setPassword(md5($entity->getPassword()));
		$entity->setOpenStatus(CommonDefine::OPEN_STATUS_TRUE);
		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getUserId();

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addBatchEntity()
	*/
	public function addBatchEntity($entityArr) {
		if (empty($entityArr)) {
			throw new Exception("数据异常");
		}

		$this->entityManager->clear();

		for ($i = 0; $i < count($entityArr); $i++) {
			$entityArr[$i]->setStatus(CommonDefine::DATA_STATUS_NORMAL);
			$entityArr[$i]->setCreateTime(new \DateTime());
			$entityArr[$i]->setLastUpdate(new \DateTime());

			$this->entityManager->merge($entityArr[$i]);
		}
		$this->entityManager->flush();

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::editEntity()
	*/
	public function editEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}

		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->flush($entity);

	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::deleteEntity()
	*/
	public function deleteEntity($id) {
		$entity = $this->getEntity($id);

		if (empty($entity)) {
			throw new Exception("数据异常");
		}

		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);

		$this->entityManager->flush($entity);

	}

}

?>