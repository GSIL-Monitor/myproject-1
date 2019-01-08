<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\AdminUserResult;

class AdminUserModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:AdminUserEntity")
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
						"adminUserId"=>$id,
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

    public function getPageAdminUser($pageIndex,$pageSize,$companyId,$userName)
    {
    
        $whereStr="au.status = :status";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
        if($companyId >0 || $companyId==-1)
        {   
            $whereStr.=" and au.companyId = :companyId";
            $paramArr["companyId"]=$companyId;
        }
        if(!empty($userName))
        {
            $whereStr.=" and au.userName = :userName";
            $paramArr["userName"]=$userName;
        }
    
        $mWhereStr=" and c.status=:status";
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('au,c.companyName')
        ->from('Clean\LibraryBundle\Entity\AdminUserEntity', 'au')
        ->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'au.companyId = c.companyId'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr);

        try
        {
            $page = new Paginator();
            $data = $page->paginate($query, $pageIndex, $pageSize);
            $dataResult=array();
            if(!empty($data))
            {
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new AdminUserResult();
                    $tempResult->setAdminUserId($data[$i][0]->getAdminUserId());
                    $tempResult->setUserName($data[$i][0]->getUserName());
                    $tempResult->setRealName($data[$i][0]->getRealName());
                    //$tempResult->setUserLevel($data[$i][0]->getUserLevel());
                    $tempResult->setCompanyId($data[$i][0]->getCompanyId());
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

    public function getAdminUserByLogin($loginName, $password)
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
		return $entity->getAdminUserId();
        
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