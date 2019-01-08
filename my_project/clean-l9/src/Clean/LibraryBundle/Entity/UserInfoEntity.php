<?php
namespace Clean\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_info")
 */
class UserInfoEntity {
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $userId;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $userName;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $nickName;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string", length=25)
	 */
	protected $phone;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $password;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $sex;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $avatar;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $lastLoginIp;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $lastLoginTime;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $loginCount;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $registerFrom;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $lastSystemMessageId;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	protected $nowSn;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $openStatus;

	/**
	 * @ORM\Column(type="string", length=40)
	 */
	protected $openId;

	/**
	 * @ORM\Column(type="string", length=40)
	 */
	protected $facebookId;

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
	 * @ORM\Column(type="string", length=32)
	 */
	protected $authenticationToken;

	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $isStartBind;

	/**
	 * @ORM\Column(type="string", length=30)
	 */
	protected $userAppVersion;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	protected $dingDongUserId;

	/**
	 * Get userId
	 *
	 * @return integer
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Set userName
	 *
	 * @param string $userName
	 * @return UserInfoEntity
	 */
	public function setUserName($userName) {
		$this->userName = $userName;

		return $this;
	}

	/**
	 * Get userName
	 *
	 * @return string
	 */
	public function getUserName() {
		return $this->userName;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return UserInfoEntity
	 */
	public function setEmail($email) {
		$this->email = $email;

		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Set phone
	 *
	 * @param string $phone
	 * @return UserInfoEntity
	 */
	public function setPhone($phone) {
		$this->phone = $phone;

		return $this;
	}

	/**
	 * Get phone
	 *
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 * @return UserInfoEntity
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Set sex
	 *
	 * @param integer sex
	 * @return UserInfoEntity
	 */
	public function setSex($sex) {
		$this->sex = $sex;

		return $this;
	}

	/**
	 * Get sex
	 *
	 * @return integer
	 */
	public function getSex() {
		return $this->sex;
	}

	/**
	 * Set lastLoginIp
	 *
	 * @param string $lastLoginIp
	 * @return UserInfoEntity
	 */
	public function setLastLoginIp($lastLoginIp) {
		$this->lastLoginIp = $lastLoginIp;

		return $this;
	}

	/**
	 * Get lastLoginIp
	 *
	 * @return string
	 */
	public function getLastLoginIp() {
		return $this->lastLoginIp;
	}

	/**
	 * Set lastLoginTime
	 *
	 * @param \DateTime $lastLoginTime
	 * @return UserInfoEntity
	 */
	public function setLastLoginTime($lastLoginTime) {
		$this->lastLoginTime = $lastLoginTime;

		return $this;
	}

	/**
	 * Get lastLoginTime
	 *
	 * @return \DateTime
	 */
	public function getLastLoginTime() {
		return $this->lastLoginTime;
	}

	/**
	 * Set openStatus
	 *
	 * @param integer $openStatus
	 * @return UserInfoEntity
	 */
	public function setOpenStatus($openStatus) {
		$this->openStatus = $openStatus;

		return $this;
	}

	/**
	 * Get openStatus
	 *
	 * @return integer
	 */
	public function getOpenStatus() {
		return $this->openStatus;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 * @return UserInfoEntity
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
	 * @return UserInfoEntity
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
	 * @return UserInfoEntity
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

	/**
	 * Set loginCount
	 *
	 * @param integer $loginCount
	 * @return UserInfoEntity
	 */
	public function setLoginCount($loginCount) {
		$this->loginCount = $loginCount;

		return $this;
	}

	/**
	 * Get loginCount
	 *
	 * @return integer
	 */
	public function getLoginCount() {
		return $this->loginCount;
	}

	/**
	 * Set registerFrom
	 *
	 * @param integer $registerFrom
	 * @return UserInfoEntity
	 */
	public function setRegisterFrom($registerFrom) {
		$this->registerFrom = $registerFrom;

		return $this;
	}

	/**
	 * Get registerFrom
	 *
	 * @return integer
	 */
	public function getRegisterFrom() {
		return $this->registerFrom;
	}

	/**
	 * Set companyId
	 *
	 * @param integer $companyId
	 * @return UserInfoEntity
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
	 * Set avatar
	 *
	 * @param string $avatar
	 * @return UserInfoEntity
	 */
	public function setAvatar($avatar) {
		$this->avatar = $avatar;

		return $this;
	}

	/**
	 * Get avatar
	 *
	 * @return string
	 */
	public function getAvatar() {
		return $this->avatar;
	}

	/**
	 * Set openId
	 *
	 * @param string $openId
	 * @return UserInfoEntity
	 */
	public function setOpenId($openId) {
		$this->openId = $openId;

		return $this;
	}

	/**
	 * Get openId
	 *
	 * @return string
	 */
	public function getOpenId() {
		return $this->openId;
	}

	/**
	 * Set facebookId
	 *
	 * @param string $facebookId
	 * @return UserInfoEntity
	 */
	public function setFacebookId($facebookId) {
		$this->facebookId = $facebookId;

		return $this;
	}

	/**
	 * Get facebookId
	 *
	 * @return string
	 */
	public function getFacebookId() {
		return $this->facebookId;
	}

	/**
	 * Set nowSn
	 *
	 * @param string $nowSn
	 * @return UserInfoEntity
	 */
	public function setNowSn($nowSn) {
		$this->nowSn = $nowSn;

		return $this;
	}

	/**
	 * Get nowSn
	 *
	 * @return string
	 */
	public function getNowSn() {
		return $this->nowSn;
	}

	/**
	 * Set lastSystemMessageId
	 *
	 * @param integer $lastSystemMessageId
	 * @return UserInfoEntity
	 */
	public function setLastSystemMessageId($lastSystemMessageId) {
		$this->lastSystemMessageId = $lastSystemMessageId;

		return $this;
	}

	/**
	 * Get lastSystemMessageId
	 *
	 * @return integer
	 */
	public function getLastSystemMessageId() {
		return $this->lastSystemMessageId;
	}

	/**
	 * Set nickName
	 *
	 * @param string $nickName
	 * @return UserInfoEntity
	 */
	public function setNickName($nickName) {
		$this->nickName = $nickName;

		return $this;
	}

	/**
	 * Get nickName
	 *
	 * @return string
	 */
	public function getNickName() {
		return $this->nickName;
	}

	/**
	 * Set authenticationToken
	 *
	 * @param string $authenticationToken
	 * @return UserInfoEntity
	 */
	public function setAuthenticationToken($authenticationToken) {
		$this->authenticationToken = $authenticationToken;

		return $this;
	}

	/**
	 * Get authenticationToken
	 *
	 * @return string
	 */
	public function getAuthenticationToken() {
		return $this->authenticationToken;
	}

	/**
	 * Set isStartBind
	 *
	 * @param integer $isStartBind
	 * @return UserInfoEntity
	 */
	public function setIsStartBind($isStartBind) {
		$this->isStartBind = $isStartBind;

		return $this;
	}

	/**
	 * Get isStartBind
	 *
	 * @return integer
	 */
	public function getIsStartBind() {
		return $this->isStartBind;
	}

	/**
	 * Set userAppVersion
	 *
	 * @param string $userAppVersion
	 * @return UserInfoEntity
	 */
	public function setUserAppVersion($userAppVersion) {
		$this->userAppVersion = $userAppVersion;

		return $this;
	}

	/**
	 * Get userAppVersion
	 *
	 * @return string
	 */
	public function getUserAppVersion() {
		return $this->userAppVersion;
	}

	/**
	 * Set dingDongUserId
	 *
	 * @param string $dingDongUserId
	 * @return UserInfoEntity
	 */
	public function setDingDongUserId($dingDongUserId) {
		$this->dingDongUserId = $dingDongUserId;

		return $this;
	}

	/**
	 * Get dingDongUserId
	 *
	 * @return string
	 */
	public function getDingDongUserId() {
		return $this->dingDongUserId;
	}

}
