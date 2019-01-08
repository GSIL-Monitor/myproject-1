<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Model\Common\Paginator;

class MachineLogModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:MachineLogEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"machineLogId" => $id,
			)
		);
		return $result;

	}

	public function getMachineLogInfoBySn($sn) {
		$result = $this->getResponsity()->findBy(
			array(
				"sn" => $sn,
			),
			array(
				"createTime" => "desc",
			)
		);
		return $result;
	}

	public function getPageMachineLog($pageIndex, $pageSize, $type = 0, $sn, $startDate = "", $endDate = "") {

		$whereStr .= " m.sn=:sn ";
		$paramArr["sn"] = $sn;

		if ($type > 0) {
			$whereStr .= " and  m.type = :type";
			$paramArr["type"] = $type;
		}

		if (!empty($startDate)) {
			$whereStr .= " and m.uploadTime>=:startDate";
			$paramArr["startDate"] = $startDate;
		}
		if (!empty($endDate)) {
			$whereStr .= " and m.uploadTime<=:endDate";
			$paramArr["endDate"] = $endDate;
		}

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('m')
			->from('Clean\LibraryBundle\Entity\MachineLogEntity', 'm')
			->setParameters($paramArr)
			->where($whereStr);

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

	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addEntity()
	*/
	public function addEntity($entity) {
		if (empty($entity)) {
			throw new Exception("数据异常");
		}

		$entity->setCreateTime(new \DateTime());

		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getMachineLogId();

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