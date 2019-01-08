<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="password_token")
 */
class PasswordTokenEntity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $passwordTokenId;
	
	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $token;
	
	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $email;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $expiredTime;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createTime;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $status;
	

    /**
     * Get passwordTokenId
     *
     * @return integer 
     */
    public function getPasswordTokenId()
    {
        return $this->passwordTokenId;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return PasswordTokenEntity
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return PasswordTokenEntity
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set expiredTime
     *
     * @param \DateTime $expiredTime
     * @return PasswordTokenEntity
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
     * @return PasswordTokenEntity
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
     * @return PasswordTokenEntity
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
}
