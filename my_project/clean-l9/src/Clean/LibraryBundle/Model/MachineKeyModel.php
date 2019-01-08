<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;

class MachineKeyModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:MachineKeyEntity")
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
						"machineKeyId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getMachineKeyInfoBySn($sn)
    {
         $result = $this->getResponsity()->findOneBy(
                array(
                        "sn"=>$sn,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                
                )
        );
        return  $result;
    }


    public function getExcelMachineKey()
    {
    
        $whereStr="m.status = :status";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
       
        $whereStr .= " and m.createTime>=:createTime";
        $paramArr["createTime"] = date("Y-m-d H:i:s", time()-6000);
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('m')
        ->from('Clean\LibraryBundle\Entity\MachineKeyEntity', 'm')
        ->setParameters($paramArr)
        ->where($whereStr);

        $data = $query->getQuery()->getResult();

        return $data;

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
		return $entity->getMachineKeyId();
        
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