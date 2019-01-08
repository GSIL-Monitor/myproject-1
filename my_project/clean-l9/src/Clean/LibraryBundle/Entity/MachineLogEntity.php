<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="machine_log")
 */
class MachineLogEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $machineLogId;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $sn;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $url;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $uploadTime;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;


    

  

    /**
     * Get machineLogId
     *
     * @return integer
     */
    public function getMachineLogId()
    {
        return $this->machineLogId;
    }

    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return MachineLogEntity
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
     * Set url
     *
     * @param string $url
     *
     * @return MachineLogEntity
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
     * Set type
     *
     * @param integer $type
     *
     * @return MachineLogEntity
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
     * Set uploadTime
     *
     * @param \DateTime $uploadTime
     *
     * @return MachineLogEntity
     */
    public function setUploadTime($uploadTime)
    {
        $this->uploadTime = $uploadTime;

        return $this;
    }

    /**
     * Get uploadTime
     *
     * @return \DateTime
     */
    public function getUploadTime()
    {
        return $this->uploadTime;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     *
     * @return MachineLogEntity
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
}
