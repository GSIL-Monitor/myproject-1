<?php
namespace Clean\LibraryBundle\Entity;

class GoodsUrlResult extends GoodsUrlEntity
{
	public function setGoodsUrlId($goodsUrlId)
	{
		$this->goodsUrlId=$goodsUrlId;
	}
	
	private $companyName;
	public function setCompanyName($companyName)
	{
		$this->companyName=$companyName;
	}
	public function getCompanyName()
	{
		return $this->companyName;
	}

}

?>