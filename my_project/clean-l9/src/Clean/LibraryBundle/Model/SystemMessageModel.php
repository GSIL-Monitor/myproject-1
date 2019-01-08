<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\SystemMessageResult;

class SystemMessageModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:SystemMessageEntity")
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
						"systemMessageId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public function getUnReadMessageList($lastSystemMessageId,$companyId,$userId)
    {
        $whereStr="sm.status = ".CommonDefine::DATA_STATUS_NORMAL;
        
        $limitDay=date("Y-m-d H:i:s", strtotime("-90 days"));
        $paramArr=array(
                "systemMessageId"=>intval($lastSystemMessageId),
                "limitDay"=>$limitDay,
                "companyId"=>$companyId,
                "userId"=>$userId
        );
        //移除已阅读限制
        $whereStr.=" and sm.systemMessageId>:systemMessageId and sm.createTime>:limitDay and (sm.companyId = :companyId or sm.companyId = -1) and (sm.toUserId = 0 or sm.toUserId = :userId )";
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('sm')
        ->from('Clean\LibraryBundle\Entity\SystemMessageEntity', 'sm')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->addOrderBy('sm.systemMessageId', 'desc');
        
        try
        {
            $data = $query->getQuery()->getResult();
            return $data;
        }
        catch(NoResultException $e)
        {
        }
        
        return null;
    }

    public function getReadMessageList($lastSystemMessageId,$companyId,$userId)
    {
        $whereStr="sm.status = ".CommonDefine::DATA_STATUS_NORMAL;
        
        $limitDay=date("Y-m-d H:i:s", strtotime("-90 days"));
        $paramArr=array(
                "systemMessageId"=>intval($lastSystemMessageId),
                "limitDay"=>$limitDay,
                "companyId"=>$companyId,
                "userId"=>$userId
        );
        //移除已阅读限制
        $whereStr.=" and sm.systemMessageId<=:systemMessageId and sm.createTime>:limitDay and (sm.companyId = :companyId or sm.companyId = -1) and (sm.toUserId = 0 or sm.toUserId = :userId )";
        
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('sm')
        ->from('Clean\LibraryBundle\Entity\SystemMessageEntity', 'sm')
        ->setParameters($paramArr)
        ->where($whereStr)
        ->addOrderBy('sm.systemMessageId', 'desc');
        
        try
        {
            $data = $query->getQuery()->getResult();
            return $data;
        }
        catch(NoResultException $e)
        {
        }
        
        return null;
    }

    public function getPageSystemMessage($pageIndex,$pageSize,$startDate,$endDate,$companyId,$userId=0,$type=0)
    {
    
        $whereStr="sm.status = :status";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
        if(! empty($startDate))
        {
            $whereStr .= " and sm.createTime>=:startDate";
            $paramArr["startDate"] = $startDate;
        }
        if(! empty($endDate))
        {
            $whereStr .= " and sm.createTime<=:endDate";
            $paramArr["endDate"] = $endDate;
        }

        if($companyId>0 || $companyId == -1)
        {
            $whereStr .= " and sm.companyId=:companyId";
            $paramArr["companyId"] = $companyId;
        }

        if($userId >0 && $type >0)
        {
            $whereStr .= " and sm.toUserId =:toUserId";
            $paramArr["toUserId"] = $userId;
        }elseif($type > 0 && $userId <= 0)
        {
            if($type == 1)
            {
                //系统
                $whereStr .= " and sm.toUserId = :toUserId";
                $paramArr["toUserId"] = 0;
            }else
            {
                $whereStr .= " and sm.toUserId > :toUserId";
                $paramArr["toUserId"] = 0;
            }
        }elseif($userId > 0 && $type <= 0)
        {
            $whereStr .= " and sm.toUserId =:toUserId";
            $paramArr["toUserId"] = $userId;
        }

       
        $mWhereStr=" and c.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('sm,c.companyName,ui.userName,ui.nickName')
        ->from('Clean\LibraryBundle\Entity\SystemMessageEntity', 'sm')
        ->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'sm.companyId = c.companyId'.$mWhereStr
        )
        ->leftJoin('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'sm.toUserId = ui.userId'
        )
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('sm.systemMessageId', 'DESC');

        try
        {
            $page = new Paginator();
            $data = $page->paginate($query, $pageIndex, $pageSize);
            $dataResult=array();
            if(!empty($data))
            {
                $url = ConfigHandler::getCommonConfig("firmwareUrl");
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new SystemMessageResult();
                    $tempResult->setSystemMessageId($data[$i][0]->getSystemMessageId());
                    $tempResult->setMessageContent($data[$i][0]->getMessageContent());
                    $tempResult->setCreateTime($data[$i][0]->getCreateTime());
                    $tempResult->setCompanyId($data[$i][0]->getCompanyId());
                    $tempResult->setMessageType($data[$i][0]->getMessageType());
                    $tempResult->setTitle($data[$i][0]->getTitle());
                    $tempResult->setCreateTime($data[$i][0]->getCreateTime());
                    if(!$data[$i]['companyName'])
                    {
                        $companyName = "通用";
                    }else
                    {
                        $companyName = $data[$i]['companyName'];
                    }
                    $tempResult->setCompanyName($companyName);

                    if($data[$i]['userName'])
                    {
                        $tempResult->setUserName($data[$i]['userName']);
                    }else
                    {
                        $tempResult->setUserName("");
                    }

                    if($data[$i]['nickName'])
                    {
                        $tempResult->setNickName($data[$i]['nickName']);
                    }else
                    {
                        $tempResult->setNickName("");
                    }
                    
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
		return $entity->getsystemMessageId();
        
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