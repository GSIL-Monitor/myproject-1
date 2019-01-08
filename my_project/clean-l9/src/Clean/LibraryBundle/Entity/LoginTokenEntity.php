<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="login_token")
 */
class LoginTokenEntity
{
	/**
	 * @ORM\Column(type="string", length=32)
	 * @ORM\Id
	 */
	protected $loginToken;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $userId;
	
	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $loginIp;
	

    /**
     * @ORM\Column(type="string", length=16)
     */
    protected $aesKEY;

    /**
     * @ORM\Column(type="string", length=16)
     */
    protected $aesIV;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $expiredTime;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $deviceToken;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $deviceType;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $deviceNumber;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $status;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createTime;
	
	
	

    /**
     * Set loginToken
     *
     * @param string $loginToken
     * @return LoginTokenEntity
     */
    public function setLoginToken($loginToken)
    {
        $this->loginToken = $loginToken;
    
        return $this;
    }

    /**
     * Get loginToken
     *
     * @return string 
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return LoginTokenEntity
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
     * Set loginIp
     *
     * @param string $loginIp
     * @return LoginTokenEntity
     */
    public function setLoginIp($loginIp)
    {
        $this->loginIp = $loginIp;
    
        return $this;
    }

    /**
     * Get loginIp
     *
     * @return string 
     */
    public function getLoginIp()
    {
        return $this->loginIp;
    }

    /**
     * Set expiredTime
     *
     * @param \DateTime $expiredTime
     * @return LoginTokenEntity
     */
    public function setExpiredTime($expiredTime)
    {
        $this->expiredTime = $expiredTime;
    
        return $this;
    }

    /**
     * Get expiredTime
     *
     * @return \DateTime 
     */
    public function getExpiredTime()
    {
        return $this->expiredTime;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return LoginTokenEntity
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
     * Set status
     *
     * @param integer $status
     * @return LoginTokenEntity
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
     * Set deviceToken
     *
     * @param string $deviceToken
     * @return LoginTokenEntity
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;
    
        return $this;
    }

    /**
     * Get deviceToken
     *
     * @return string 
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * Set deviceType
     *
     * @param integer $deviceType
     * @return LoginTokenEntity
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    
        return $this;
    }

    /**
     * Get deviceType
     *
     * @return integer 
     */
    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /**
     * Set deviceNumber
     *
     * @param string $deviceNumber
     *
     * @return LoginTokenEntity
     */
    public function setDeviceNumber($deviceNumber)
    {
        $this->deviceNumber = $deviceNumber;

        return $this;
    }

    /**
     * Get deviceNumber
     *
     * @return string
     */
    public function getDeviceNumber()
    {
        return $this->deviceNumber;
    }

     /**
     * Set aesKEY
     *
     * @param string $aesKEY
     * @return UserInfoEntity
     */
    public function setAesKEY($aesKEY)
    {
        $this->aesKEY = $aesKEY;
    
        return $this;
    }

    /**
     * Get aesKEY
     *
     * @return string 
     */
    public function getAesKEY()
    {
        return $this->aesKEY;
    }

     /**
     * Set aesIV
     *
     * @param string $aesIV
     * @return UserInfoEntity
     */
    public function setAesIV($aesIV)
    {
        $this->aesIV = $aesIV;
    
        return $this;
    }

    /**
     * Get aesIV
     *
     * @return string 
     */
    public function getAesIV()
    {
        return $this->aesIV;
    }
   
    
}
