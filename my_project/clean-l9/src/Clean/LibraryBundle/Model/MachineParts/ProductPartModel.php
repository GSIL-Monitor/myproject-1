<?php
namespace Clean\LibraryBundle\Model\MachineParts;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\MachineParts\ProductPartEntity;
use Clean\LibraryBundle\Entity\MachineParts\ProductPartResult;

class ProductPartModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryMachinePartsBundle:ProductPartEntity")
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
						"productPartId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function isExistSn($sn)
    {
       $result = $this->getResponsity()->findOneBy(
                array(
                        "sn"=>$sn,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        if(! empty($result))
        {
            return $result;
        }
        return false;
    }


    public function getPagePartProduct($pageIndex,$pageSize,$sn,$name,$place,$version,$startDate,$endDate)
    {
    
        $whereStr="pp.status = :status ";
    
        $paramArr=array(
					"status"=>CommonDefine::DATA_STATUS_NORMAL
        );

        if(!empty($sn))
        {
            $whereStr.=" and pp.sn = :sn";
            $paramArr["sn"]=$sn;
        }

        if(!empty($name))
        {
            $whereStr.=" and pp.name = :name";
            $paramArr["name"]=$name;
        }

        if(!empty($place))
        {
            $whereStr.=" and pp.place = :place";
            $paramArr["place"]=$place;
        }

        if(!empty($version))
        {
            $whereStr.=" and pp.version = :version";
            $paramArr["version"]=$version;
        }

        if(! empty($startDate))
        {
            $whereStr .= " and pp.createTime>=:startDate";
            $paramArr["startDate"] = $startDate;
        }
        if(! empty($endDate))
        {
            $whereStr .= " and pp.createTime<=:endDate";
            $paramArr["endDate"] = $endDate;
        }
    
        $mWhereStr=" and pt.status=:status";

    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('pp,pt.name')
        ->from('Clean\LibraryBundle\Entity\MachineParts\ProductPartEntity', 'pp')
        ->leftJoin('Clean\LibraryBundle\Entity\MachineParts\ProductTypeEntity', 'pt',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'pp.type = pt.type'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('pp.productPartId', 'DESC');

        try
        {
            $page = new Paginator();
            $data = $page->paginate($query, $pageIndex, $pageSize);
            $dataResult = array();
            if(!empty($data))
            {
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new ProductPartResult();
                    $tempResult->setProductPartId($data[$i][0]->getProductPartId());
                    $tempResult->setSn($data[$i][0]->getSn());
                    $tempResult->setName($data[$i][0]->getName());
                    $tempResult->setPlace($data[$i][0]->getPlace());
                    $tempResult->setVersion($data[$i][0]->getVersion());
                    $tempResult->setMark($data[$i][0]->getMark());
                    $tempResult->setCreateTime($data[$i][0]->getCreateTime());
                    $tempResult->setType($data[$i][0]->getType());
                    $tempResult->setTypeName($data[$i]['name']);
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

        return $entity->getProductPartId();
        
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
			//throw new Exception("数据异常");
            return false;
		}
	
		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		
		$this->entityManager->flush($entity);
        
    }
 
	



	
	
}


?>