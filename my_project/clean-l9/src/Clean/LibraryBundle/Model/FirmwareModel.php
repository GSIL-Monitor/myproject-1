<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\FirmwareResult;
use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Model\Common\Paginator;
use Common\Utils\ConfigHandler;

class FirmwareModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:FirmwareEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"firmwareId" => $id,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getFirmwareByVersionCodeAndCompanyId($versionCode, $companyId) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"companyId" => $companyId,
				"versionCode" => $versionCode,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getPageFirmware($pageIndex, $pageSize, $intVersionCode, $searchType) {

		$whereStr = "f.status = :status";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);

		$mWhereStr = " and c.status=:status";

		// if ($companyId > 0 || $companyId == -1) {
		// 	$whereStr .= " and f.companyId=:companyId";
		// 	$paramArr["companyId"] = $companyId;
		// }

		if ($intVersionCode && $searchType) {
			$paramArr["intVersionCode"] = $intVersionCode;
			if ($searchType == 1) {
				$whereStr .= " and f.intVersionCode=:intVersionCode";
			} else {
				$whereStr .= " and f.intVersionCode>:intVersionCode";
			}
		}

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('f,c.companyName')
			->from('Clean\LibraryBundle\Entity\FirmwareEntity', 'f')
			->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
				\Doctrine\ORM\Query\Expr\Join::WITH,
				'f.companyId = c.companyId' . $mWhereStr
			)
			->setParameters($paramArr)
			->where($whereStr)
			->orderBy('f.firmwareId', 'DESC');

		try
		{
			$page = new Paginator();
			$data = $page->paginate($query, $pageIndex, $pageSize);
			$dataResult = array();
			if (!empty($data)) {
				$url = ConfigHandler::getCommonConfig("firmwareUrl");
				for ($i = 0; $i < count($data); $i++) {
					$tempResult = new FirmwareResult();
					$tempResult->setFirmwareId($data[$i][0]->getFirmwareId());
					$tempResult->setVersionCode($data[$i][0]->getVersionCode());
					$tempResult->setFirmwareName($data[$i][0]->getFirmwareName());
					$tempResult->setUrl($url . $data[$i][0]->getUrl());
					$tempResult->setDescription($data[$i][0]->getDescription());
					$tempResult->setCompanyId($data[$i][0]->getCompanyId());
					$tempResult->setCheckCode($data[$i][0]->getCheckCode());
					$tempResult->setCreateTime($data[$i][0]->getCreateTime());
					$tempResult->setLastUpdate($data[$i][0]->getLastUpdate());

					$tempResult->setDisplayVersionCode($data[$i][0]->getDisplayVersionCode());
					$tempResult->setSns($data[$i][0]->getSns());

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

	public function getFirmwareInfoByVersion($companyId, $versionCode = 0) {
		$whereStr = "f.status = " . CommonDefine::DATA_STATUS_NORMAL . " and (f.companyId=:companyId or f.companyId = -1) ";
		$paramArr = array(
			"companyId" => $companyId,
		);

		if ($versionCode) {
			$whereStr .= " and f.intVersionCode =:versionCode";
			$paramArr["versionCode"] = $versionCode;
		}
		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('f')
			->from('Clean\LibraryBundle\Entity\FirmwareEntity', 'f')
			->setParameters($paramArr)
			->where($whereStr)
			->addOrderBy('f.versionCode', 'desc')
			->setMaxResults(1);

		try
		{
			$data = $query->getQuery()->getResult();
			if ($data) {
				return $data[0];
			} else {
				return null;
			}

		} catch (NoResultException $e) {
		}

		return null;
	}

	public function getSilentLatestFirmwareInfo($companyId, $newCode = 0, $versionCode = 0, $sn = 0) {
		$whereStr = "f.status = " . CommonDefine::DATA_STATUS_NORMAL . " and (f.companyId=:companyId or f.companyId = -1) ";
		$paramArr = array(
			"companyId" => $companyId,
		);

		$whereStr .= " and f.isAutoUpdate = 1";

		if ($newCode) {
			$whereStr .= " and f.intVersionCode <=:newCode";
			$paramArr["newCode"] = $newCode;
		}

		if ($versionCode) {
			$whereStr .= " and f.intVersionCode >:versionCode";
			$paramArr["versionCode"] = $versionCode;
		}

		if ($sn) {
			$whereStr .= " and ( locate(:sn,f.sns)>0 or f.sns=0 )";
			$paramArr["sn"] = $sn;
		}

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('f')
			->from('Clean\LibraryBundle\Entity\FirmwareEntity', 'f')
			->setParameters($paramArr)
			->where($whereStr)
			->addOrderBy('f.intVersionCode', 'desc')
			->setMaxResults(1);

		try
		{
			$data = $query->getQuery()->getResult();
			if ($data) {
				return $data[0];
			} else {
				return null;
			}

		} catch (NoResultException $e) {
		}

		return null;
	}

	public function getLatestFirmwareInfo($companyId, $newCode = 0, $versionCode = 0, $sn = 0) {
		$whereStr = "f.status = " . CommonDefine::DATA_STATUS_NORMAL . " and (f.companyId=:companyId or f.companyId = -1) ";
		$paramArr = array(
			"companyId" => $companyId,
		);

		if ($newCode) {
			$whereStr .= " and f.intVersionCode <=:newCode";
			$paramArr["newCode"] = $newCode;
		}

		if ($versionCode) {
			$whereStr .= " and f.intVersionCode >:versionCode";
			$paramArr["versionCode"] = $versionCode;
		}

		if ($sn) {
			$whereStr .= " and ( locate(:sn,f.sns)>0 or f.sns=0 )";
			$paramArr["sn"] = $sn;
		}

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('f')
			->from('Clean\LibraryBundle\Entity\FirmwareEntity', 'f')
			->setParameters($paramArr)
			->where($whereStr)
			->addOrderBy('f.intVersionCode', 'desc')
			->setMaxResults(1);

		try
		{
			$data = $query->getQuery()->getResult();
			if ($data) {
				return $data[0];
			} else {
				return null;
			}

		} catch (NoResultException $e) {
		}

		return null;
	}

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addEntity()
	*/
	public function addEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}
		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getFirmwareId();

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
			return false;
		}

		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);

		$this->entityManager->flush($entity);

	}

}

?>