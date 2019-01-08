<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\MachineResult;
use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Model\Common\Paginator;

class MachineModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:MachineEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"machineId" => $id,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getPageMachine($pageIndex, $pageSize, $companyId, $sn, $startDate, $endDate, $version, $searchType) {

		$whereStr = "m.status = :status";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);
		if ($companyId > 0 || $companyId == -1) {
			$whereStr .= " and  m.companyId = :companyId";
			$paramArr["companyId"] = $companyId;
		}

		if (!empty($startDate)) {
			$whereStr .= " and m.createTime>=:startDate";
			$paramArr["startDate"] = $startDate;
		}
		if (!empty($endDate)) {
			$whereStr .= " and m.createTime<=:endDate";
			$paramArr["endDate"] = $endDate;
		}

		if (!empty($sn)) {
			$whereStr .= " and m.sn=:sn";
			$paramArr["sn"] = $sn;
		}

		if ($version && $searchType) {
			$paramArr["version"] = $version;
			if ($searchType == 1) {
				$whereStr .= " and m.version=:version";
			} else {
				$whereStr .= " and m.version>:version";
			}
		}

		$mWhereStr = " and c.status=:status";

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('m,c.companyName')
			->from('Clean\LibraryBundle\Entity\MachineEntity', 'm')
			->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
				\Doctrine\ORM\Query\Expr\Join::WITH,
				'm.companyId = c.companyId' . $mWhereStr
			)
			->setParameters($paramArr)
			->where($whereStr);

		try
		{
			$page = new Paginator();
			$data = $page->paginate($query, $pageIndex, $pageSize);
			$dataResult = array();
			if (!empty($data)) {
				for ($i = 0; $i < count($data); $i++) {
					$tempResult = new MachineResult();
					$tempResult->setMachineId($data[$i][0]->getMachineId());
					$tempResult->setSn($data[$i][0]->getSn());
					$tempResult->setCompanyId($data[$i][0]->getCompanyId());
					$tempResult->setMachineName($data[$i][0]->getMachineName());
					$tempResult->setCreateTime($data[$i][0]->getCreateTime());
					$tempResult->setLastUpdate($data[$i][0]->getLastUpdate());
					$tempResult->setVersion($data[$i][0]->getVersion());
					$tempResult->setHardware($data[$i][0]->getHardware());
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

	public function isExistSn($sn) {
		$entity = $this->getMachineBySn($sn);
		if (!empty($entity)) {
			return $entity;
		}
		return false;
	}

	public function getMachineBySn($sn) {
		$entity = $this->getResponsity()->findOneBy(array(
			"sn" => $sn,
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		));
		return $entity;
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
		return $entity->getMachineId();

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