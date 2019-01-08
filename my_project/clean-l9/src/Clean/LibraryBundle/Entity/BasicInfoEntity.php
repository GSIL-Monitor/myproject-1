<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="basic_info")
 */ 
class BasicInfoEntity
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $basicInfoId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $companyId;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected  $description;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected  $lang;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;
    
    
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
     * @ORM\Column(type="smallint")
     */
    protected $type;

    

    /**
     * Get basicInfoId
     *
     * @return integer
     */
    public function getBasicInfoId()
    {
        return $this->basicInfoId;
    }

    /**
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return BasicInfoEntity
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
     * Set description
     *
     * @param string $description
     *
     * @return BasicInfoEntity
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
     * Set content
     *
     * @param string $content
     *
     * @return BasicInfoEntity
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

   
    /**
     * Set status
     *
     * @param integer $status
     *
     * @return BasicInfoEntity
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
     * @return BasicInfoEntity
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
     * @return BasicInfoEntity
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
     * Set status
     *
     * @param integer $status
     *
     * @return BasicInfoEntity
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return BasicInfoEntity
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }



}
