<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\ErrorCodeResult;

class ErrorCodeVersionModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:ErrorCodeVersionEntity")
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
						"errorCodeVersionId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getHighVersion($intVersion)
    {	
    	$whereStr="ecv.status = :status";
    	$whereStr.=" and ecv.intVersion > :intVersion";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL,
                    "intVersion"=>$intVersion
        );
        
    	$query = $this->entityManager->createQueryBuilder();
        $query
        ->select('ecv')
        ->from('Clean\LibraryBundle\Entity\ErrorCodeVersionEntity', 'ecv')
        ->setParameters($paramArr)
        ->where($whereStr);

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

    public function getExistEntity()
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        if($result)
        {
            return $result;
        }else
        {
            return array();
        }
        
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
		return $entity->getErrorCodeVersionId();
        
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