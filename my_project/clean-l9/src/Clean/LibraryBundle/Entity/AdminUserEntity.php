<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="admin_user")
 */
class AdminUserEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $adminUserId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $companyId;
    
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
     * @ORM\Column(type="string", length=45)
     */
    protected  $userLevel;
    
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
     * @ORM\Column(type="integer")
     */
    protected $loginCount;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected  $lastLoginIp;

    

    /**
     * Get adminUserId
     *
     * @return integer
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return AdminUserEntity
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return AdminUserEntity
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
     * @return AdminUserEntity
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
     * @return AdminUserEntity
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
     * @param string $userLevel
     *
     * @return AdminUserEntity
     */
    public function setUserLevel($userLevel)
    {
        $this->userLevel = $userLevel;

        return $this;
    }

    /**
     * Get userLevel
     *
     * @return string
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
     * @return AdminUserEntity
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
     * @return AdminUserEntity
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
     * @return AdminUserEntity
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
     * @return AdminUserEntity
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
     * Set loginCount
     *
     * @param integer $loginCount
     *
     * @return AdminUserEntity
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

    /**
     * Set lastLoginIp
     *
     * @param string $lastLoginIp
     *
     * @return AdminUserEntity
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
}
