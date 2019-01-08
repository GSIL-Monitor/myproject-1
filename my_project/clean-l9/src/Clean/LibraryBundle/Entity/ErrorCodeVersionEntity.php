<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="error_code_version")
 */
class ErrorCodeVersionEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $errorCodeVersionId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $intVersion;

    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $version;


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
     * Get errorCodeVersionId
     *
     * @return integer
     */
    public function getErrorCodeVersionId()
    {
        return $this->errorCodeVersionId;
    }

    /**
     * Set intVersion
     *
     * @param integer $intVersion
     *
     * @return ErrorCodeVersionEntity
     */
    public function setIntVersion($intVersion)
    {
        $this->intVersion = $intVersion;

        return $this;
    }

    /**
     * Get intVersion
     *
     * @return integer
     */
    public function getIntVersion()
    {
        return $this->intVersion;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return ErrorCodeVersionEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return ErrorCodeVersionEntity
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
     * @return ErrorCodeVersionEntity
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
     * @return ErrorCodeVersionEntity
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
