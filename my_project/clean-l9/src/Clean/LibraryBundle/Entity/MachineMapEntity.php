<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="machine_map")
 */
class MachineMapEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $machineMapId;
    
    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $sn;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $backupMd5;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $url;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;


    

    /**
     * Get machineMapId
     *
     * @return integer
     */
    public function getMachineMapId()
    {
        return $this->machineMapId;
    }

    /**
     * Set sn
     *
     * @param string $sn
     *
     * @return MachineMapEntity
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
     * Set backupMd5
     *
     * @param string $backupMd5
     *
     * @return MachineMapEntity
     */
    public function setBackupMd5($backupMd5)
    {
        $this->backupMd5 = $backupMd5;

        return $this;
    }

    /**
     * Get backupMd5
     *
     * @return string
     */
    public function getBackupMd5()
    {
        return $this->backupMd5;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return MachineMapEntity
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
     * Set createTime
     *
     * @param \DateTime $createTime
     *
     * @return MachineMapEntity
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
