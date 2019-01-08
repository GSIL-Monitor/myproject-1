<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\ErrorCodeResult;
use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Model\Common\Paginator;

class ErrorCodeModel extends BaseModelAbstract {
	private function getResponsity($entity = "CleanLibraryBundle:ErrorCodeEntity") {
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
		     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
	*/
	public function getEntity($id) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"errorCodeId" => $id,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getErrorCodeInfoByCompanyId($companyId) {
		$whereStr = "ec.status = :status and (ec.companyId=:companyId or ec.companyId = -1) ";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
			"companyId" => $companyId,
		);

		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('ec.code,ec.errType,ec.enMsg,ec.chMsg,ec.koMsg')
			->from('Clean\LibraryBundle\Entity\ErrorCodeEntity', 'ec')
			->setParameters($paramArr)
			->where($whereStr);

		try
		{
			$data = $query->getQuery()->getResult();
			if ($data) {
				return $data;
			} else {
				return array();
			}
		} catch (NoResultException $e) {
		}

		return null;
	}

	public function getEntityByCode($code) {
		$result = $this->getResponsity()->findOneBy(
			array(
				"code" => $code,
				"status" => CommonDefine::DATA_STATUS_NORMAL,
			)
		);
		return $result;

	}

	public function getPageErrorCode($pageIndex, $pageSize, $companyId) {

		$whereStr = "ec.status = :status";

		$paramArr = array(
			"status" => CommonDefine::DATA_STATUS_NORMAL,
		);
		if ($companyId > 0 || $companyId == -1) {
			$whereStr .= " and ec.companyId = :companyId";
			$paramArr["companyId"] = $companyId;
		}

		$mWhereStr = " and c.status=:status";
		$query = $this->entityManager->createQueryBuilder();
		$query
			->select('ec,c.companyName')
			->from('Clean\LibraryBundle\Entity\ErrorCodeEntity', 'ec')
			->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
				\Doctrine\ORM\Query\Expr\Join::WITH,
				'ec.companyId = c.companyId' . $mWhereStr
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
					$tempResult = new ErrorCodeResult();
					$tempResult->setErrorCodeId($data[$i][0]->getErrorCodeId());
					$tempResult->setCode($data[$i][0]->getCode());
					$tempResult->setErrType($data[$i][0]->getErrType());
					$tempResult->setEnMsg($data[$i][0]->getEnMsg());
					$tempResult->setChMsg($data[$i][0]->getChMsg());
					$tempResult->setKoMsg($data[$i][0]->getKoMsg());
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
		return $entity->getErrorCodeId();

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