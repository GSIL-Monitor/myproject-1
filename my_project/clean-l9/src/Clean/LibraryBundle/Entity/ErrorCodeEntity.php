<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="error_code")
 */
class ErrorCodeEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $errorCodeId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $errorCodeVersionId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $code;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $errType;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $enMsg;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $chMsg;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $koMsg;

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
	 * Get errorCodeId
	 *
	 * @return integer
	 */
	public function getErrorCodeId() {
		return $this->errorCodeId;
	}

	/**
	 * Set companyId
	 *
	 * @param integer $companyId
	 *
	 * @return ErrorCodeEntity
	 */
	public function setCompanyId($companyId) {
		$this->companyId = $companyId;

		return $this;
	}

	/**
	 * Get companyId
	 *
	 * @return integer
	 */
	public function getCompanyId() {
		return $this->companyId;
	}

	/**
	 * Set errorCodeVersionId
	 *
	 * @param integer $errorCodeVersionId
	 *
	 * @return ErrorCodeEntity
	 */
	public function setErrorCodeVersionId($errorCodeVersionId) {
		$this->errorCodeVersionId = $errorCodeVersionId;

		return $this;
	}

	/**
	 * Get errorCodeVersionId
	 *
	 * @return integer
	 */
	public function getErrorCodeVersionId() {
		return $this->errorCodeVersionId;
	}

	/**
	 * Set code
	 *
	 * @param string $code
	 *
	 * @return ErrorCodeEntity
	 */
	public function setCode($code) {
		$this->code = $code;

		return $this;
	}

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Set errType
	 *
	 * @param string $errType
	 *
	 * @return ErrorCodeEntity
	 */
	public function setErrType($errType) {
		$this->errType = $errType;

		return $this;
	}

	/**
	 * Get errType
	 *
	 * @return string
	 */
	public function getErrType() {
		return $this->errType;
	}

	/**
	 * Set enMsg
	 *
	 * @param string $enMsg
	 *
	 * @return ErrorCodeEntity
	 */
	public function setEnMsg($enMsg) {
		$this->enMsg = $enMsg;

		return $this;
	}

	/**
	 * Get enMsg
	 *
	 * @return string
	 */
	public function getEnMsg() {
		return $this->enMsg;
	}

	/**
	 * Set chMsg
	 *
	 * @param string $chMsg
	 *
	 * @return ErrorCodeEntity
	 */
	public function setChMsg($chMsg) {
		$this->chMsg = $chMsg;

		return $this;
	}

	/**
	 * Get koMsg
	 *
	 * @return string
	 */
	public function getKoMsg() {
		return $this->koMsg;
	}

	/**
	 * Set koMsg
	 *
	 * @param string $koMsg
	 *
	 * @return ErrorCodeEntity
	 */
	public function setKoMsg($koMsg) {
		$this->koMsg = $koMsg;

		return $this;
	}

	/**
	 * Get chMsg
	 *
	 * @return string
	 */
	public function getChMsg() {
		return $this->chMsg;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 *
	 * @return ErrorCodeEntity
	 */
	public function setStatus($status) {
		$this->status = $status;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Set createTime
	 *
	 * @param \DateTime $createTime
	 *
	 * @return ErrorCodeEntity
	 */
	public function setCreateTime($createTime) {
		$this->createTime = $createTime;

		return $this;
	}

	/**
	 * Get createTime
	 *
	 * @return \DateTime
	 */
	public function getCreateTime() {
		return $this->createTime;
	}

	/**
	 * Set lastUpdate
	 *
	 * @param \DateTime $lastUpdate
	 *
	 * @return ErrorCodeEntity
	 */
	public function setLastUpdate($lastUpdate) {
		$this->lastUpdate = $lastUpdate;

		return $this;
	}

	/**
	 * Get lastUpdate
	 *
	 * @return \DateTime
	 */
	public function getLastUpdate() {
		return $this->lastUpdate;
	}
}
