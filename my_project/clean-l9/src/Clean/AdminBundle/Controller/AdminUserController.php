<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\AdminUserEntity;
use Clean\LibraryBundle\Entity\AdminUserResult;

class AdminUserController extends BaseController
{   

	public function adminUserPageListAction()
	{	
		if($this->CompanyId == -1)
        {
            $isAdmin = true;
        }else{
            $isAdmin = false;
        }
        $lmcc = $this->get("library_model_clean_company");
		$companyInfo = $lmcc->getEntityList();
        return $this->render("CleanAdminBundle:AdminUser:adminUserPageList.html.twig", array("isAdmin"=>$isAdmin,"companyInfo"=>$companyInfo));
	}

	public function getAdminUserPageListAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$lmcau = $this->get("library_model_clean_adminuser");
			$companyId = intval($this->requestParameter("companyId"));
			$userName = intval($this->requestParameter("userName"));
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
			$result = $lmcau->getPageAdminUser($pageIndex,$pageSize,$companyId,$userName);
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


	public function addAdminUserAction()
	{
		try
		{	
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			return $this->render("CleanAdminBundle:AdminUser:addAdminUser.html.twig",array("companyInfo" => $companyInfo));
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addAdminUserSubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
			$userName = $this->requestParameter("userName");
			$realName = $this->requestParameter("realName");
			//$userLevel = intval($this->requestParameter("userLevel"));
			$companyId = intval($this->requestParameter("companyId"));
			$password = $this->requestParameter("password");
			if($companyId<=0 && $companyId != -1 )
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			if(!$userName || !$password)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}

			if( strlen($userName) < 4 )
			{
				return new Response($this->getAPIResultJson("E02000", "用户名不能小于4位", ""));
			}

			if($companyId == -1)
			{
				$userLevel = 1;
			}else
			{
				$userLevel = 2;
			}
			$lmcau = $this->get("library_model_clean_adminuser");
			$adminUserInfo = $lmcau->getEntityByName($userName);
			if($adminUserInfo)
			{
				return new Response($this->getAPIResultJson("E02000", "名字已经存在，请勿重复添加", ""));
			}

			$entity = new AdminUserEntity();
			$entity->setUserName($userName);
			$entity->setPassword(md5($password));
			$entity->setCompanyId($companyId);
			$entity->setUserLevel($userLevel);
			if($realName)
			{
				$entity->setRealName($realName);
			}
			$adminUserId = $lmcau->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editAdminUserAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $adminUserId = intval($this->requestParameter("adminUserId"));
            if($adminUserId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
			$companyInfo = $lmcc->getEntityList();
			$lmcau = $this->get("library_model_clean_adminuser");
			$adminUserEntity = $lmcau->getEntity($adminUserId);
			if(!$adminUserEntity)
			{
                return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$adminUserinfo = new AdminUserResult();	
            $adminUserinfo->setAdminUserId($adminUserEntity ->getAdminUserId());
            $adminUserinfo->setUserName($adminUserEntity ->getUserName());
            $adminUserinfo->setRealName($adminUserEntity ->getRealName());
            $adminUserinfo->setCompanyId($adminUserEntity ->getCompanyId());
            $adminUserinfo->setCreateTime($adminUserEntity ->getCreateTime());
            $companyId=$adminUserEntity->getCompanyId();
			if($companyId == -1)
			{
				$companyName = '通用';
			}else
			{
				$companyNameInfo = $lmcc->getEntity($companyId);
				$companyName = $companyNameInfo->getCompanyName();
			}
			$adminUserinfo->setCompanyName($companyName);
			return $this->render("CleanAdminBundle:AdminUser:editAdminUser.html.twig",array("companyInfo" => $companyInfo,"adminUserinfo"=>$adminUserinfo));
			
		}
		catch (\Exception $ex)
		{	
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editAdminUserSubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			} 
			$adminUserId = intval($this->requestParameter("adminUserId"));
			$userName = $this->requestParameter("userName");
			$realName = $this->requestParameter("realName");
			$companyId = intval($this->requestParameter("companyId"));
			$password = $this->requestParameter("password");
			if($companyId<=0 && $companyId != -1 )
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
            if($adminUserId <= 0 || empty($userName))
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			if($companyId == -1)
			{
				$userLevel = 1;
			}else
			{
				$userLevel = 2;
			}

			$lmcau = $this->get("library_model_clean_adminuser");
			$adminUserEntity = $lmcau->getEntity($adminUserId);
			$adminUserEntity->setUserName($userName);
			$adminUserEntity->setCompanyId($companyId);
			$adminUserEntity->setUserLevel($userLevel);
			if($realName)
			{
				$adminUserEntity->setRealName($realName);
			}
			if($password && strlen($password) > 10)
			{
				$adminUserEntity->setPassword(md5(md5($password)));
			}
			$adminUser = $lmcau->editEntity($adminUserEntity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
			
		}
		catch (\Exception $ex)
		{	
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}


    public function deleteAdminUserListAction()  
    {
        try
        {
            if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}
            
            $adminUserIdList = $this->requestParameter("adminUserIdList");
            if(!$adminUserIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $adminUserIdList);
            $lmcau = $this->get("library_model_clean_adminuser");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i]>0)
            	{
            		$lmcau->deleteEntity($listArr[$i]);
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