<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\GoodsUrlEntity;
use Clean\LibraryBundle\Entity\GoodsUrlResult;

class GoodsUrlController extends BaseController
{   

	public function goodsUrlPageListAction()
	{
        return $this->render("CleanAdminBundle:GoodsUrl:goodsUrlPageList.html.twig", array());
	}

	public function getGoodsUrlPageListAction()
	{
		try
		{	

			$companyId = $this->CompanyId;
			if($companyId <=0 && $companyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcgu = $this->get("library_model_clean_goodsurl");
			$pageIndex = intval($this->requestParameter("pageIndex"));
            if(empty($pageIndex))
            {
                $pageIndex = 1;
            }
            
            $pageSize = intval($this->requestParameter("pageSize"));
            if(empty($pageSize))
            {
                $pageSize = 30;
            }

            if( $companyId == -1)
            {
            	 $companyId = 0;
            }
            
			$result = $lmcgu->getPageGoodsUrl($pageIndex,$pageSize,$companyId);
			if($result)
			{
				return new Response($this->getAPIResultJson("N00000", "数据读取成功", $result));
			}else
			{
				return new Response($this->getAPIResultJson("E02000", "数据读取失败", ""));
			}
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}


	public function addGoodsUrlAction()
	{
		try
		{	
			$lmcc = $this->get("library_model_clean_company");
	        if($this->CompanyId == -1)
	        {
	            $isAdmin = true;
	            $companyInfo = $lmcc->getEntityList();
	        }else{
	            $isAdmin = false;
	            $result = $lmcc->getEntity($this->CompanyId);
	            $companyInfo = array();
	            $companyInfo[] = $result;
	        }
			return $this->render("CleanAdminBundle:GoodsUrl:addGoodsUrl.html.twig",array("companyInfo" => $companyInfo,"isAdmin"=> $isAdmin));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addGoodsUrlSubmitAction()
	{
		try
		{	
			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
			$companyId = intval($this->requestParameter("companyId"));
			if($companyId <= 0 && $companyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$url = $this->requestParameter("url");

			if(!$url)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			if(!preg_match('/^^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+$/', $url))
			{
				return new Response($this->getAPIResultJson("E02000", "请输入正确格式网址", ""));
			}
			
			$lmcgu = $this->get("library_model_clean_goodsurl");
			$goodsUrlInfo = $lmcgu->getGoodsUrlByCompanyId($companyId);
			if($goodsUrlInfo)
			{
				return new Response($this->getAPIResultJson("E02000", "该公司链接已经存在，请勿重复添加", ""));
			}
			$entity = new GoodsUrlEntity();
			$entity->setCompanyId($companyId);
			$entity->setUrl($url);
			$GoodsUrlId = $lmcgu->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editGoodsUrlAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
               return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $goodsUrlId = intval($this->requestParameter("goodsUrlId"));
            if($goodsUrlId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcgu = $this->get("library_model_clean_goodsurl");
			$goodsUrlEntity = $lmcgu->getEntity($goodsUrlId);
			return $this->render("CleanAdminBundle:GoodsUrl:editGoodsUrl.html.twig",array("goodsUrlInfo"=>$goodsUrlEntity));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editGoodsUrlSubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			} 
			$goodsUrlId = intval($this->requestParameter("goodsUrlId"));
            if($goodsUrlId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$url = $this->requestParameter("url");
			if(!$url)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcgu = $this->get("library_model_clean_goodsurl");
			$goodsUrlEntity = $lmcgu->getEntity($goodsUrlId);

			$goodsUrlEntity->setUrl($url);
			$goodsUrl = $lmcgu->editEntity($goodsUrlEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}


    public function deleteGoodsUrlListAction()  
    {
        try
        {
            if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $goodsUrlIdList = $this->requestParameter("goodsUrlIdList");
            if(!$goodsUrlIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $goodsUrlIdList);
            $lmcgu = $this->get("library_model_clean_goodsurl");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i]>0)
            	{
            		$lmcgu->deleteEntity($listArr[$i]);
            	}
            	
            }
            return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
        }
        catch(\Exception $ex)
        {

            $this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
            return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
        }
    }
   
}
?>