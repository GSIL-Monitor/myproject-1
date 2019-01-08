<?php
namespace Clean\LibraryBundle\Model\MachineParts;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\MachineParts\PartAdminUserEntity;

class PartAdminUserModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryMachinePartsBundle:PartAdminUserEntity")
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
						"partAdminUserId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }


    public function getEntityByName($name)
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "userName"=>$name,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getAdminUserByLogin($loginName,$password)
    {
        if(empty($loginName) || empty($password))
        {
            return null;
        }

        $query = $this->getResponsity()->createQueryBuilder("u")
        ->where("lower(u.userName)=lower(:loginName) and u.password=:password and u.status=:status")
        ->setParameters(array(
                "loginName" => $loginName,
                "password" => $password,
                "status" => CommonDefine::DATA_STATUS_NORMAL 
        ))
        ->setMaxResults(1)
        ->getQuery();

        $data = $query->getResult();

        if(!empty($data))
        {
            return $data[0];
        }else{
            return null;
        }
    }


    public function getPagePartAdminUser($pageIndex,$pageSize,$userName)
    {
    
        $whereStr="au.status = :status ";
    
        $paramArr=array(
					"status"=>CommonDefine::DATA_STATUS_NORMAL
        );

        if(!empty($userName))
        {
            $whereStr.=" and au.userName = :userName";
            $paramArr["userName"]=$userName;
        }

    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('au')
        ->from('Clean\LibraryBundle\Entity\MachineParts\PartAdminUserEntity', 'au')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('au.partAdminUserId', 'DESC');

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
        $entity->setLastLoginTime(new \DateTime());
        $entity->setPassword(md5($entity->getPassword()));
        $entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
        $entity->setCreateTime(new \DateTime());
        $entity->setLastUpdate(new \DateTime());
        
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        return $entity->getPartAdminUserId();
        
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