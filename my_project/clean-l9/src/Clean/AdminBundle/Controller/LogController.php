<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\LogEntity;


class LogController extends BaseController
{   

    public function debugPageListAction()
    {   
        if($this->CompanyId == -1)
        {
            $isAdmin = true;
        }else{
            $isAdmin = false;
        }
        $lmcc = $this->get("library_model_clean_company");
        $companyInfo = $lmcc->getEntityList();
        return $this->render("CleanAdminBundle:Debug:debugPageList.html.twig", array("isAdmin"=>$isAdmin,"companyInfo"=>$companyInfo));
    }

	public function logPageListAction()
	{
        $sn = intval($this->requestParameter("sn"));
        return $this->render("CleanAdminBundle:Log:logPageList.html.twig", array(
            "sn"=>$sn
        ));
	}

	public function getLogPageListAction()
	{
		try
		{	
			$lmcl = $this->get("library_model_clean_log");
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
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
            $startDate = $this->requestParameter("startDate");
            $endDate = $this->requestParameter("endDate");

            $sn = $this->requestParameter("sn");

			$result = $lmcl->getPageLog($pageIndex,$pageSize,$startDate,$endDate,$sn);
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
			$this->writeErrorLog($ex, __CLASS__, __FUNCTION__);
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

    public function deleteLogListAction()  
    {
        try
        {
            if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $logIdList = $this->requestParameter("logIdList");
            if(!$logIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $logIdList);
            $lmcl = $this->get("library_model_clean_log");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i]>0)
            	{
            		$lmcl->deleteEntity($listArr[$i]);
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

    //    public function uploadLogFileAction()
	// {
	// 	$filePath=ConfigHandler::getCommonConfig("LogPath");
	// 	$uploadResult = UploadFileHandler::requestUploadTypeFile("file", $filePath, false);
	// 	if(!is_array($uploadResult))
	// 	{
	// 		return new Response($this->getAPIResultJson("E01000", strval($uploadResult), ""));
	// 	}
	// 	$fileName=$uploadResult["fileName"];
	// 	$uploadResult["fileName"]=str_replace($filePath, "", $fileName);
	// 	return new Response($this->getAPIResultJson("N00000", "上传成功", $uploadResult));
	// }
	
   
    
}
?>