<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\CompanyEntity;
use Clean\LibraryBundle\Entity\CompanyResult;

class CompanyController extends BaseController
{   

	public function companyPageListAction()
	{	
		if($this->CompanyId == -1)
        {
            $isAdmin = true;
        }else{
            $isAdmin = false;
        }
        return $this->render("CleanAdminBundle:Company:companyPageList.html.twig", array("isAdmin"=>$isAdmin));
	}

	public function getCompanyPageListAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
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
			$result = $lmcc->getPageCompany($pageIndex,$pageSize);
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

	public function addCompanySubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$companyName = $this->requestParameter("companyName");

			if(!$companyName)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityByName($companyName);
			if($companyInfo)
			{
				return new Response($this->getAPIResultJson("E02000", "公司名字已经存在，请勿重复添加", ""));
			}
			$entity = new CompanyEntity();
			$entity->setCompanyName($companyName);
			$companyId = $lmcc->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

    public function editCompanySubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			} 
			$companyId = intval($this->requestParameter("companyId"));
			$companyName = $this->requestParameter("companyName");
            if($companyId <= 0 || empty($companyName))
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			$lmcc = $this->get("library_model_clean_company");
			$companyEntity = $lmcc->getEntity($companyId);
			$companyEntity->setCompanyName($companyName);
		
			$company = $lmcc->editEntity($companyEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}


    public function deleteCompanyListAction()  
    {
        try
        {
            if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $companyIdList = $this->requestParameter("companyIdList");
            if(!$companyIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $companyIdList);
            $lmcc = $this->get("library_model_clean_company");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i]>0)
            	{
            		$lmcc->deleteEntity($listArr[$i]);
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