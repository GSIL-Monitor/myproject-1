<?php
namespace Clean\LibraryBundle\Entity\MachineParts;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="part_admin_user")
 */
class PartAdminUserEntity
{ 
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $partAdminUserId;

     /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $userName;

    
    /**
     * @ORM\Column(type="string", length=32)
     */
    protected  $password;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $realName;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $userLevel;

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
     * @ORM\Column(type="datetime")
     */
    protected $lastLoginTime;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected  $lastLoginIp;


    /**
     * @ORM\Column(type="integer")
     */
    protected  $loginCount;

    
    
  

   

    

    /**
     * Get partAdminUserId
     *
     * @return integer
     */
    public function getPartAdminUserId()
    {
        return $this->partAdminUserId;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return PartAdminUserEntity
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return PartAdminUserEntity
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set realName
     *
     * @param string $realName
     *
     * @return PartAdminUserEntity
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * Get realName
     *
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Set userLevel
     *
     * @param integer $userLevel
     *
     * @return PartAdminUserEntity
     */
    public function setUserLevel($userLevel)
    {
        $this->userLevel = $userLevel;

        return $this;
    }

    /**
     * Get userLevel
     *
     * @return integer
     */
    public function getUserLevel()
    {
        return $this->userLevel;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return PartAdminUserEntity
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
     *
     * @return PartAdminUserEntity
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
     *
     * @return PartAdminUserEntity
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

    /**
     * Set lastLoginTime
     *
     * @param \DateTime $lastLoginTime
     *
     * @return PartAdminUserEntity
     */
    public function setLastLoginTime($lastLoginTime)
    {
        $this->lastLoginTime = $lastLoginTime;

        return $this;
    }

    /**
     * Get lastLoginTime
     *
     * @return \DateTime
     */
    public function getLastLoginTime()
    {
        return $this->lastLoginTime;
    }

    /**
     * Set lastLoginIp
     *
     * @param string $lastLoginIp
     *
     * @return PartAdminUserEntity
     */
    public function setLastLoginIp($lastLoginIp)
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }

    /**
     * Get lastLoginIp
     *
     * @return string
     */
    public function getLastLoginIp()
    {
        return $this->lastLoginIp;
    }

    /**
     * Set loginCount
     *
     * @param integer $loginCount
     *
     * @return PartAdminUserEntity
     */
    public function setLoginCount($loginCount)
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    /**
     * Get loginCount
     *
     * @return integer
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }
}
