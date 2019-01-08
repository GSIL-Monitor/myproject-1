<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Common\CommonDefine;
use Clean\LibraryBundle\Entity\LoginTokenEntity;
use \Doctrine\ORM\NoResultException;
use Common\Utils\IPHandler;
use Clean\LibraryBundle\Model\BaseModel;

class LoginTokenModel extends BaseModel
{
	private function getResponsity($entity="CleanLibraryBundle:LoginTokenEntity")
	{
		return $this->entityManager->getRepository($entity);
	}
	
	public function getLoginTokenByToken($token)
	{
		try {
			$result = $this->getResponsity()->findOneBy(
					array(
							"loginToken"=>$token,
							"status"=>CommonDefine::DATA_STATUS_NORMAL
			));
		}
		catch(NoResultException $e) {
			return null;
		}
	
		return  $result;
	}

	public function getLoginTokenByUserId($userId)
	{
		try {
			$result = $this->getResponsity()->findOneBy(
					array(
							"userId"=>$userId,
							"status"=>CommonDefine::DATA_STATUS_NORMAL
					),
					array(
						"createTime"=>"desc"
					)
				);
		}
		catch(NoResultException $e) {
			return null;
		}
	
		return  $result;
	}
	
	public function getPhoneLoginTokenByUserId($userId)
	{
		$whereStr="lt.status = ".CommonDefine::DATA_STATUS_NORMAL;
		$whereStr.=" and lt.deviceType <> ".CommonDefine::DEVICE_TYPE_WEB;
		$paramArr=array();

		$whereStr.=" and lt.userId = :userId";
		$paramArr["userId"] = $userId;

	
		$query = $this->getResponsity()->createQueryBuilder("lt");
		$query
		->where($whereStr)
		->setParameters($paramArr)
		->orderBy('lt.createTime', 'desc');
		try
		{
			$result=$query->getQuery()->getResult();
			if(!empty($result))
			{
				return $result[0];
			}
		}
		catch(NoResultException $e)
		{
		}
	
		return null;
	}
	
	public function getRangePhoneLoginTokenList($maxUserId,$minUserId,$countryCode)
	{
	    $maxUserId=intval($maxUserId);
	    $minUserId=intval($minUserId);
	    if($maxUserId<$minUserId)
	    {
	    	return null;
	    }
	    
	    $whereStr="lt.status = ".CommonDefine::DATA_STATUS_NORMAL;
	    $whereStr.=" and lt.deviceType <> ".CommonDefine::DEVICE_TYPE_WEB;
	    $paramArr=array();
	
	    $whereStr.=" and lt.userId <= :maxUserId";
	    $paramArr["maxUserId"] = $maxUserId;
	    
	    $whereStr.=" and lt.userId >= :minUserId";
	    $paramArr["minUserId"] = $minUserId;
	
	    $userWhereStr= " and ui.status=".CommonDefine::DATA_STATUS_NORMAL;
	    $userWhereStr=" and ui.phone like :countryCode";
	    $paramArr["countryCode"] = $countryCode."%";
	    
	    $query = $this->entityManager->createQueryBuilder();
	    $query->select('lt')
	    ->from('Inmotion\LibraryBundle\Entity\LoginTokenEntity', 'lt')
	    ->innerJoin('Inmotion\LibraryBundle\Entity\UserInfoEntity', 'ui',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'lt.userId = ui.userId'.$userWhereStr)
        ->where($whereStr)
        ->setParameters($paramArr);
	
// 	    $query = $this->getResponsity()->createQueryBuilder("lt");
// 	    $query
// 	    ->where($whereStr)
// 	    ->setParameters($paramArr);
	    try
	    {
	        $result=$query->getQuery()->getResult();
	        return $result;
	    }
	    catch(NoResultException $e)
	    {
	    }
	
	    return null;
	}
	
	public function getLoginTokenListByUserId($userId)
	{
		try {
			$result = $this->getResponsity()->findBy(
					array(
							"userId"=>$userId,
							"status"=>CommonDefine::DATA_STATUS_NORMAL
					));
		}
		catch(NoResultException $e) {
			return null;
		}
	
		return  $result;
	}
	
	public function isDeviceRegistered($deviceNumber)
	{
		if(!empty($deviceNumber))
		{
			$result = $this->getResponsity()->findOneBy(
					array(
							"deviceNumber"=>$deviceNumber
			));
			if(!empty($result))
			{
				return true;
			}
		}
		return false;
	}
	
	public function addLoginToken(LoginTokenEntity $entity)
	{
		if(empty($entity))
		{
			throw new Exception("数据异常");
		}
		
		if(empty($entity->getDeviceNumber()))
		{
			$entity->setDeviceNumber(IPHandler::getClientIP());
		}
		
		$entity->setStatus(CommonDefine::DATA_STATUS_NORMAL);
		$entity->setCreateTime(new \DateTime());
		$this->entityManager->persist($entity);
		$this->entityManager->flush($entity);
		return $entity->getLoginToken();
	}
	
	public function deletePhoneLoginTokenByUserId($userId)
	{
		$list=$this->getLoginTokenListByUserId($userId);
		if(empty($list))
		{
			return;
		}
		for($i=0;$i<count($list);$i++)
		{
			$deviceType=$list[$i]->getDeviceType();
			$loginToken=$list[$i]->getLoginToken();
			if($deviceType!=CommonDefine::DEVICE_TYPE_WEB)
			{
				$this->deleteLoginToken($loginToken);
			}
		}
	}
	
	public function deleteLoginToken($loginToken)
	{
		$entity=$this->getLoginTokenByToken($loginToken);
		
		if(empty($entity))
		{
			throw new Exception("数据异常");
		}
		
		$entity->setStatus(CommonDefine::DATA_STATUS_DELETE);
		
		$this->entityManager->flush($entity);
	}
	
	
	
}


?>