<?php
namespace Clean\LibraryBundle\Model;

use Clean\LibraryBundle\Model\BaseModelAbstract;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\ConfigHandler;
use Clean\LibraryBundle\Entity\UserMachineResult;
use Clean\LibraryBundle\Model\Common\Paginator;
use Clean\LibraryBundle\Entity\Common\PageResult;

class UserMachineModel extends BaseModelAbstract
{
    private function getResponsity($entity="CleanLibraryBundle:UserMachineEntity")
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
                        "userMachineId"=>$id,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getOwerBySn($sn)
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "sn"=>$sn,
                        "userType"=>1,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getEntityByUserIdAndSn($userId,$sn)
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "userId"=>$userId,
                        "sn"=>$sn,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getOneEntityByUserId($userId)
    {
        $result = $this->getResponsity()->findOneBy(
                array(
                        "userId"=>$userId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }


    public function getEntityBySn($sn)
    {
        $result = $this->getResponsity()->findBy(
                array(
                        "sn"=>$sn,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getEntityByUserId($userId)
    {
        $result = $this->getResponsity()->findBy(
                array(
                        "userId"=>$userId,
                        "status"=>CommonDefine::DATA_STATUS_NORMAL
                )
        );
        return  $result;
        
    }

    public function getPageUserMachine($pageIndex, $pageSize,$userId)
    {
    
        $whereStr="um.status = :status and um.userId=:userId";
    
        $paramArr=array(
                    "status"=>CommonDefine::DATA_STATUS_NORMAL,
                    "userId"=>$userId
        );
    
        $mWhereStr=" and m.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('um,m.machineName')
        ->from('Clean\LibraryBundle\Entity\UserMachineEntity', 'um')
        ->leftJoin('Clean\LibraryBundle\Entity\MachineEntity', 'm',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'um.sn = m.sn'.$mWhereStr
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
                    $tempResult = new UserMachineResult();
                    $tempResult->setUserMachineId($data[$i][0]->getUserMachineId());
                    $tempResult->setUserId($data[$i][0]->getUserId());
                    $tempResult->setUserType($data[$i][0]->getUserType());
                    $tempResult->setSn($data[$i][0]->getSn());
                    $tempResult->setNoteName($data[$i][0]->getNoteName());
                    $tempResult->setMachineName($data[$i]['machineName']);
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

    public function getUserAllMachine($userId)
    {
    
        $whereStr="um.status = :status and um.userId = :userId";
    
        $paramArr=array(
                "userId"=>$userId,
                "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
    
        $mWhereStr=" and m.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('um,m.machineName')
        ->from('Clean\LibraryBundle\Entity\UserMachineEntity', 'um')
        ->leftJoin('Clean\LibraryBundle\Entity\MachineEntity', 'm',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'um.sn = m.sn'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr);

        try
        {
            $data = $query->getQuery()->getResult();
            if(!empty($data))
            {
                $dataResult=array();
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new UserMachineResult();
                    $tempResult->setUserMachineId($data[$i][0]->getUserMachineId());
                    $tempResult->setUserId($data[$i][0]->getUserId());
                    $tempResult->setUserType($data[$i][0]->getUserType());
                    $tempResult->setSn($data[$i][0]->getSn());
                    $tempResult->setNoteName($data[$i][0]->getNoteName());
                    $tempResult->setMachineName($data[$i]['machineName']);
                    array_push($dataResult, $tempResult);
                }
                return $dataResult;
                 
            }
        }
        catch(NoResultException $e)
        {
        }
    
        return null;

    }

    public function getUserMachineInfo($userId,$sn)
    {
    
        $whereStr="um.status = :status and um.userId = :userId and um.sn = :sn";
    
        $paramArr=array(
                "userId"=>$userId,
                "sn"=>$sn,
                "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
    
        $mWhereStr=" and m.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('um,m.machineName')
        ->from('Clean\LibraryBundle\Entity\UserMachineEntity', 'um')
        ->leftJoin('Clean\LibraryBundle\Entity\MachineEntity', 'm',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'um.sn = m.sn'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->setMaxResults(1)
        ->where($whereStr);

        try
        {
            $data = $query->getQuery()->getResult();
            if(!empty($data))
            {   
                $data = $data[0];
                $tempResult = new UserMachineResult();
                $tempResult->setUserMachineId($data[0]->getUserMachineId());
                $tempResult->setUserId($data[0]->getUserId());
                $tempResult->setUserType($data[0]->getUserType());
                $tempResult->setSn($data[0]->getSn());
                $tempResult->setNoteName($data[0]->getNoteName());
                $tempResult->setMachineName($data['machineName']);
                return $tempResult;
            }
        }
        catch(NoResultException $e)
        {
        }
    
        return null;

    }


    public  function getUserRobotListBySN($sn)
    {
        $whereStr="um.status = :status and um.sn = :sn";
        $paramArr=array(
                "sn"=>$sn,
                "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
        $whereStr .= " and um.sn = ui.nowSn";
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('um.sn,um.userId')
        ->from('Clean\LibraryBundle\Entity\UserMachineEntity', 'um')
        ->leftJoin('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'um.userId = ui.userId'
        )
        ->setParameters($paramArr)
        ->where($whereStr);
        $data = $query->getQuery()->getResult();
        return  $data;
    }

    public function getMachineAllUser($sn)
    {
    
        $whereStr="um.status = :status and um.sn = :sn";
    
        $paramArr=array(
                "sn"=>$sn,
                "status"=>CommonDefine::DATA_STATUS_NORMAL
        );
    
        $mWhereStr=" and ui.status=:status";
    
        $query = $this->entityManager->createQueryBuilder();
        $query
        ->select('um,ui.userName,ui.avatar,ui.nickName')
        ->from('Clean\LibraryBundle\Entity\UserMachineEntity', 'um')
        ->leftJoin('Clean\LibraryBundle\Entity\UserInfoEntity', 'ui',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'um.userId = ui.userId'.$mWhereStr
        )
        ->setParameters($paramArr)
        ->where($whereStr);

        try
        {
            $data = $query->getQuery()->getResult();
            if(!empty($data))
            {
                $dataResult=array();
                for($i=0;$i<count($data);$i++)
                {
                    $tempResult = new UserMachineResult();
                    $tempResult->setUserMachineId($data[$i][0]->getUserMachineId());
                    $tempResult->setUserId($data[$i][0]->getUserId());
                    $tempResult->setUserType($data[$i][0]->getUserType());
                    $tempResult->setSn($data[$i][0]->getSn());
                    $tempResult->setNoteName($data[$i][0]->getNoteName());
                    $tempResult->setUserName($data[$i]['userName']);
                    $tempResult->setNickName($data[$i]['nickName']);
                    $tempResult->setAvatar($data[$i]['avatar']);
                    array_push($dataResult, $tempResult);
                }
                return $dataResult;
            }
        }
        catch(NoResultException $e)
        {
        }
    
        return null;

    }

    public function isExistUserSn($sn,$userId)
    {
        $entity = $this->getResponsity()->findOneBy(array(
                "sn" => $sn,
                "userId" => $userId,
                "status" => CommonDefine::DATA_STATUS_NORMAL
        ));
        if(! empty($entity))
        {
            return $entity;
        }
        return false;
    }

    public function isExistUserBySn($sn)
    {
        $entity = $this->getResponsity()->findOneBy(array(
                "sn" => $sn,
                "status" => CommonDefine::DATA_STATUS_NORMAL
        ));
        if(! empty($entity) && $entity->getUserId()>0)
        {
            return true;
        }
        return false;
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
        return $entity->getUserMachineId();
        
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

    public function deleteAllUserMachine($sn)
    {
        $userInfo = $this->getMachineAllUser($sn);
        if(!empty($userInfo))
        {
            for($i=0;$i<count($userInfo);$i++)
            {
                $this->deleteEntity($userInfo[$i]->getUserMachineId());
            }
        }
        return true;
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