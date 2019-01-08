<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_version")
 */
class AppVersionEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $appVersionId;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $versionCode;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $appName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $url;

     /**
     * @ORM\Column(type="string", length=1500)
     */
    protected $description;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $appType;

     /**
     * @ORM\Column(type="integer")
     */
    protected $companyId;
    
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
     * Get appVersionId
     *
     * @return integer
     */
    public function getAppVersionId()
    {
        return $this->appVersionId;
    }

    /**
     * Set versionCode
     *
     * @param string $versionCode
     *
     * @return AppVersionEntity
     */
    public function setVersionCode($versionCode)
    {
        $this->versionCode = $versionCode;

        return $this;
    }

    /**
     * Get versionCode
     *
     * @return string
     */
    public function getVersionCode()
    {
        return $this->versionCode;
    }

    /**
     * Set appName
     *
     * @param string $appName
     *
     * @return AppVersionEntity
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;

        return $this;
    }

    /**
     * Get appName
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return AppVersionEntity
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AppVersionEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set appType
     *
     * @param integer $appType
     *
     * @return AppVersionEntity
     */
    public function setAppType($appType)
    {
        $this->appType = $appType;

        return $this;
    }

    /**
     * Get appType
     *
     * @return integer
     */
    public function getAppType()
    {
        return $this->appType;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return AppVersionEntity
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
     * @return AppVersionEntity
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
     * @return AppVersionEntity
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
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return FirmwareEntity
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
}
