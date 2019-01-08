<?php
namespace Clean\LibraryBundle\Model\MachineParts;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\MachineParts\ProductTypeEntity;

class ProductTypeModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryMachinePartsBundle:ProductTypeEntity")
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
						"productTypeId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getEntityList()
    {
        $result = $this->getResponsity()->findBy(
                array(
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getProductTypeByName($name)
    {
        $result = $this->getResponsity()->findOneBy(array(
                "name" => $name,
                "status" => CommonDefine::DATA_STATUS_NORMAL 
        ));
        return $result;
    }

    public function getProductTypeByType($type)
    {
        $result = $this->getResponsity()->findOneBy(array(
                "type" => $type,
                "status" => CommonDefine::DATA_STATUS_NORMAL 
        ));
        return $result;
    }


    public function getPageTypeProduct($pageIndex,$pageSize,$name)
    {
    
        $whereStr="pt.status = :status ";
    
        $paramArr=array(
					"status"=>CommonDefine::DATA_STATUS_NORMAL
        );

        if(!empty($name))
        {
            $whereStr.=" and pt.name = :name";
            $paramArr["name"]=$name;
        }

    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('pt')
        ->from('Clean\LibraryBundle\Entity\MachineParts\ProductTypeEntity', 'pt')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('pt.productTypeId', 'DESC');

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
            throwException("数据异常");
        }
        $entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
        $entity->setCreateTime(new \DateTime());
        $entity->setLastUpdate(new \DateTime());
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);

        return $entity->getProductTypeId();
        
    }

  

	/* (non-PHPdoc)
     * @see \Clean\LibraryBundle\Model\BaseModelAbstract::addBatchEntity()
     */
    public function addBatchEntity($entityArr)
    {
        if(empty($entityArr))
	    {
	        throwException("数据异常");
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
			throwException("数据异常");
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
			//thrthrow new ExceptionowException("数据异常");
            return false;
		}
	
		$entity->setLastUpdate(new \DateTime());
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		
		$this->entityManager->flush($entity);
        
    }
 
	



	
	
}


?>