<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\GoodsUrlResult;


class GoodsUrlModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:GoodsUrlEntity")
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
						"goodsUrlId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getGoodsUrlByCompanyId($companyId)
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "companyId"=>$companyId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getGoodsUrl($companyId)
    {   
        if(!$companyId)
        {
            $companyId = -1;
        }
        $whereStr="gu.status = ".CommonDefine::DATA_STATUS_NORMAL. " and gu.companyId=:companyId ";
        $paramArr=array(
                "companyId"=>$companyId
        );

        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('gu')
        ->from('Clean\LibraryBundle\Entity\GoodsUrlEntity', 'gu')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->setMaxResults(1);
        
        try
        {
            $data = $query->getQuery()->getResult();
            if($data)
            {
                return $data[0];
            }else
            {
                return null;
            }
            
        }
        catch(NoResultException $e)
        {
        }
        
        return null;
    }

    public function getPageGoodsUrl($pageIndex, $pageSize)
    {
    
        $whereStr="gu.status = :status";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
    
        $mWhereStr=" and c.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('gu,c.companyName')
        ->from('Clean\LibraryBundle\Entity\GoodsUrlEntity', 'gu')
        ->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'gu.companyId = c.companyId'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('gu.goodsUrlId', 'DESC');

        try
        {
            $page = new Paginator();
            $data = $page->paginate($query, $pageIndex, $pageSize);
            $dataResult=array();
            if(!empty($data))
            {
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new GoodsUrlResult();
                    $tempResult->setUrl($data[$i][0]->getUrl());
                    $tempResult->setGoodsUrlId($data[$i][0]->getGoodsUrlId());
                    $tempResult->setCreateTime($data[$i][0]->getCreateTime());
                    if(!$data[$i]['companyName'])
                    {
                        $companyName = "通用";
                    }else
                    {
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
		return $entity->getGoodsUrlId();
        
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
			return false;
		}
	
		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		
		$this->entityManager->flush($entity);
        
    }
 
	



	
	
}


?>