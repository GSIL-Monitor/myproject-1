<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_machine")
 */
class UserMachineEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $userMachineId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $sn;

     /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $noteName;
    
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $userType;
    
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $status;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdate;

    /**
     * Get userMachineId
     *
     * @return integer 
     */
    public function getUserMachineId()
    {
        return $this->userMachineId;
    }


    /**
     * Set userId
     *
     * @param integer $userId
     * @return UserInfoEntity
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }


    /**
     * Set sn
     *
     * @param string $sn
     * @return UserInfoEntity
     */
    public function setSn($sn)
    {
        $this->sn = $sn;
    
        return $this;
    }

    /**
     * Get sn
     *
     * @return string 
     */
    public function getSn()
    {
        return $this->sn;
    }

     /**
     * Set noteName
     *
     * @param string $noteName
     * @return UserInfoEntity
     */
    public function setNoteName($noteName)
    {
        $this->noteName = $noteName;
    
        return $this;
    }

    /**
     * Get noteName
     *
     * @return string 
     */
    public function getNoteName()
    {
        return $this->noteName;
    }


      /**
     * Set userType
     *
     * @param integer $userType
     * @return UserInfoEntity
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
    
        return $this;
    }

    /**
     * Get userType
     *
     * @return integer 
     */
    public function getUserType()
    {
        return $this->userType;
    }


    /**
     * Set status
     *
     * @param integer $status
     * @return UserInfoEntity
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return UserInfoEntity
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    
        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return UserInfoEntity
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    
        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    
    
 

    
}
