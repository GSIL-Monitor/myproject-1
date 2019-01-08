<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;
use Clean\LibraryBundle\Entity\BasicInfoResult;
use Clean\LibraryBundle\Entity\BasicInfoEntity;

class BasicInfoModel extends BaseModelAbstract
{
	private function getResponsity($entity="CleanLibraryBundle:BasicInfoEntity")
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
						"basicInfoId"=>$id,
						"status"=>CommonDefine::DATA_STATUS_NORMAL
				)
		);
		return  $result;
        
    }

    public  function getBasicInfoByTypeAndCompanyId($type,$companyId,$lang='')
    {   

        $arr = array(
                        "type"=>$type,
                        "companyId"=>$companyId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                );
        if($lang)
        {
            $arr["lang"] = $lang;
        }
    	$result = $this->getResponsity()->findOneBy(
				$arr
		);
		return  $result;
    }

    public  function getDesByTypeAndCompanyId($type,$companyId,$lang='')
    {   
        $arr =  array(
                        "type"=>$type,
                        "companyId"=>$companyId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                );

        if($lang)
        {
            $arr["lang"] = $lang;
        }
        $result = $this->getResponsity()->findBy(
               $arr
        );
        return  $result;
    }


    public  function getBasicInfoByDesAndCompanyId($description,$companyId,$lang)
    {   
        $arr =  array(
                        "description"=>$description,
                        "companyId"=>$companyId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                );
        if($lang)
        {
            $arr["lang"] = $lang;
        }

        $result = $this->getResponsity()->findOneBy(
            $arr   
        );
        return  $result;
    }


    public function getPageBasicInfo($pageIndex, $pageSize,$companyId)
    {
    
        $whereStr="bi.status = :status ";
    
        $paramArr=array(
					"status"=>CommonDefine::DATA_STATUS_NORMAL
        );

        if($companyId >0 || $companyId==-1)
        {   
            $whereStr.=" and bi.companyId=:companyId";
            $paramArr["companyId"] = $companyId;
        }

        $whereStr .= "and ( bi.type = 1 or bi.type =2)";

        $mWhereStr=" and c.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('bi,c.companyName')
        ->from('Clean\LibraryBundle\Entity\BasicInfoEntity', 'bi')
        ->leftJoin('Clean\LibraryBundle\Entity\CompanyEntity', 'c',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'bi.companyId = c.companyId'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr)
        ->orderBy('bi.basicInfoId', 'DESC');

        try
        {
            $page = new Paginator();
            $data = $page->paginate($query, $pageIndex, $pageSize);
            $dataResult=array();
            if(!empty($data))
            {
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new BasicInfoResult();
                    $tempResult->setBasicInfoId($data[$i][0]->getBasicInfoId());
                    $tempResult->setCompanyId($data[$i][0]->getCompanyId());
                    $tempResult->setDescription($data[$i][0]->getDescription());
                    if($data[$i][0]->getLang() == "cn")
                    {
                        $tempResult->setLang("简体中文");
                    }elseif($data[$i][0]->getLang() == "en")
                    {
                        $tempResult->setLang("ENGLISH");
                    }
                    $tempResult->setType($data[$i][0]->getType());
                    $tempResult->setCompanyName($data[$i]['companyName']);
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

        return $entity->getBasicInfoId();
        
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