<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\LogResult;

class LogModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:LogEntity")
	{
		return $this->entityManager->getRepository($entity);
	}
	/* (non-PHPdoc)
     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::getEntity()
     */
    public function getEntity($id)
    {
        $result = $this->getResponsity()->findOneBy(
				array(
						"logId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getPageLog($pageIndex,$pageSize,$startDate,$endDate,$sn)
    {
    
        $whereStr="l.status = :status";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
       
        if(! empty($startDate))
        {
            $whereStr .= " and l.createTime>=:startDate";
            $paramArr["startDate"] = $startDate;
        }
        if(! empty($endDate))
        {
            $whereStr .= " and l.createTime<=:endDate";
            $paramArr["endDate"] = $endDate;
        }

        if(! empty($sn))
        {
            $whereStr .= " and l.sn =:sn";
            $paramArr["sn"] = $sn;
        }
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('l')
        ->from('Clean\LibraryBundle\Entity\LogEntity', 'l')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('l.logId', 'DESC');

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
        }
        catch(NoResultException $e)
        {
        }
    
        return null;

    }


  
   
	/* (non-PHPdoc)
     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addEntity()
     */
    public function addEntity($entity)
    {
        if(empty($entity))
		{
			throw new Exception("数据异常");
		}
		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$entity->setLastUpdate(new \DateTime());
		
		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getLogId();
        
    }

  

	/* (non-PHPdoc)
     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addBatchEntity()
     */
    public function addBatchEntity($entityArr)
    {   
        if(empty($entityArr))
	    {
	        throw new Exception("数据异常");
	    }
	    
	    $this->entityManager->clear();
	    for($i=0;$i< count($entityArr);$i++)
	    {
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
    public function editEntity($entity)
    {
        if(empty($entity))
		{
			throw new Exception("数据异常");
		}
		
		$entity->setLastUpdate(new \DateTime());
		$this->entityManager->flush($entity);
    }

	/* (non-PHPdoc)
     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::deleteEntity()
     */
    public function deleteEntity($id)
    {
        $entity=$this->getEntity($id);
		
		if(empty($entity))
		{
			throw new Exception("数据异常");
		}
	
		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		
		$this->entityManager->flush($entity);
        
    }
 
	



	
	
}


?>