<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\BasicInfoEntity;

class ProductionController extends BaseController
{   

	public function productionPageListAction()
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
        return $this->render("CleanAdminBundle:BasicInfo:productIntroductionPageList.html.twig", array(
            "companyInfo" => $companyInfo,
            "isAdmin"=> $isAdmin
        ));
	}

	public function getProductionPageListAction()
	{
		try
		{	
			$lmcbi = $this->get("library_model_clean_basicinfo");
			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }

			$companyId = $this->CompanyId;
			if($companyId <=0 && $companyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			if($companyId == -1)
            {
            	$companyId=intval($this->requestParameter("companyId"));
            }
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

			$result = $lmcbi->getPageBasicInfo($pageIndex,$pageSize,$companyId);
			if($result)
			{
				return new Response($this->getAPIResultJson("N00000", "数据修改成功", $result));
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

	public function editProductionAction()
	{
		try
		{	
			$basicInfoId = intval($this->requestParameter("basicInfoId"));
			if($basicInfoId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$lmcbi = $this->get("library_model_clean_basicinfo");
			$basicInfo = $lmcbi->getEntity($basicInfoId);
			$data = $basicInfo ? $basicInfo : array();	
			$lmcc = $this->get("library_model_clean_company");
			if($this->CompanyId == -1)
	        {
	            $companyInfo = $lmcc->getEntityList();
	        }else{
	            $result = $lmcc->getEntity($this->CompanyId);
	            $companyInfo = array();
	            $companyInfo[] = $result;
	        }		
			return $this->render("CleanAdminBundle:BasicInfo:editProductIntroduction.html.twig",array("basicInfo" => $data,"companyInfo" => $companyInfo));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editProductionSubmitAction()
	{
		try
		{	
			$content = $this->requestParameter("cnContent");
			$lang = $this->requestParameter("lang");
			$basicInfoId = intval($this->requestParameter("basicInfoId"));
			$description = $this->requestParameter("description");
			if($basicInfoId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcbi = $this->get("library_model_clean_basicinfo");
			$basicInfo = $lmcbi->getEntity($basicInfoId);
			if(!$basicInfo)
			{
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}
			if(!empty($content))
			{
				$basicInfo->setContent($content);
			}
			if(!empty($lang))
			{
				$basicInfo->setLang($lang);
			}
			if(!empty($description))
			{
				$basicInfo->setDescription($description);
			}
			
			$basicInfo = $lmcbi->editEntity($basicInfo);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addProductionAction()
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
			return $this->render("CleanAdminBundle:BasicInfo:addProductIntroduction.html.twig",array("companyInfo" => $companyInfo,"isAdmin"=> $isAdmin));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addProductionSubmitAction()
	{
		try
		{	

			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
			$content = $this->requestParameter("cnContent");
			$companyId = intval($this->requestParameter("companyId"));
			$type = intval($this->requestParameter("type"));
			$lang = $this->requestParameter("lang");
			if($companyId <= 0 || $type <= 0 || !$lang)
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcbi = $this->get("library_model_clean_basicinfo");
			if($type == 1)
			{
				$basicInfo = $lmcbi->getBasicInfoByTypeAndCompanyId($type,$companyId,$lang);
				$message = "该公司已经存在主页面，请勿重复添加";
				$description = "";
			}else
			{	
				$description = $this->requestParameter("description");
				if(!$description)
				{
					return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
				}
				$basicInfo = $lmcbi->getBasicInfoByDesAndCompanyId($description,$companyId,$lang);
				$message = "该公司该选项已存在，请勿重复添加";
			}
			
			if($basicInfo)
			{
				return new Response($this->getAPIResultJson("E02000", $message, ""));
			}
			$entity = new BasicInfoEntity();
			$entity->setCompanyId($companyId);
			$entity->setType($type);
			$entity->setDescription($description);
			$entity->setLang($lang);
			if(!empty($content))
			{
				$entity->setContent($content);
			}
			
			$basicInfo = $lmcbi->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function deleteProductionAction()  
    {
        try
        {
   			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
            
            $basicInfoId = intval($this->requestParameter("basicInfoId"));
            if($basicInfoId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            $lmcbi = $this->get("library_model_clean_basicinfo");
            
            $res = $lmcbi->deleteEntity($basicInfoId);
            if(!$res)
            {
            	return new Response($this->getAPIResultJson("N00000", "数据不存在", ""));
            }
            return new Response($this->getAPIResultJson("N00000", "删除成功", ""));
        }
        catch(\Exception $ex)
        {
            $this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
            return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
        }
    }

    public function deleteProductionListAction()  
    {
        try
        {
   			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
            
            $basicIdList = $this->requestParameter("basicIdList");
            if(!$basicIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $basicIdList);
            $lmcbi = $this->get("library_model_clean_basicinfo");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i] >0)
            	{
            		$lmcbi->deleteEntity($listArr[$i]);
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