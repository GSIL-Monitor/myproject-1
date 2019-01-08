<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="machine_key")
 */
class MachineKeyEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $machineKeyId;

    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected  $sn;

    /**
     * @ORM\Column(type="text")
     */
    protected $publicKey;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $privateKey;
    
    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $pushKey;

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
     * Get machineKeyId
     *
     * @return integer
     */
    public function getMachineKeyId()
    {
        return $this->machineKeyId;
    }


    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return MachineKeyEntity
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
     * Set publicKey
     *
     * @param string $publicKey
     *
     * @return MachineKeyEntity
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set privateKey
     *
     * @param string $privateKey
     *
     * @return MachineKeyEntity
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Get privateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    
    

    /**
     * Set pushKey
     *
     * @param string $pushKey
     *
     * @return MachineKeyEntity
     */
    public function setPushKey($pushKey)
    {
        $this->pushKey = $pushKey;

        return $this;
    }

    /**
     * Get pushKey
     *
     * @return string
     */
    public function getPushKey()
    {
        return $this->pushKey;
    }


    /**
     * Set status
     *
     * @param integer $status
     *
     * @return FirmwareEntity
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
     * @return FirmwareEntity
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
     * @return FirmwareEntity
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
