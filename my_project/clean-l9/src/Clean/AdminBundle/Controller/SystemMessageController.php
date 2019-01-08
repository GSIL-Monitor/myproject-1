<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Clean\LibraryBundle\Common\CommonDefine;
use Common\Utils\LogHandler;
use Common\Utils\HtmlHandler;
use Common\Utils\ConfigHandler;
use Common\Utils\File\UploadFileHandler;
use Clean\LibraryBundle\Entity\MessageContentEntity;
use Clean\LibraryBundle\Entity\SystemMessageEntity;
use Clean\LibraryBundle\Entity\SystemMessageResult;

class SystemMessageController extends BaseController
{   

	public function systemMessagePageListAction()
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
        return $this->render("CleanAdminBundle:SystemMessage:systemMessagePageList.html.twig", array(
            "companyInfo" => $companyInfo,
            "isAdmin"=> $isAdmin
        ));
	}

	public function getSystemMessagePageListAction()
	{
		try
		{	
			$lmcsm = $this->get("library_model_clean_systemmessage");
			// if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }

			$companyId = $this->CompanyId;
			if($companyId <=0 && $companyId != -1)
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
            if($companyId == -1)
            {
            	$companyId=intval($this->requestParameter("companyId"));
            }
            $type=intval($this->requestParameter("type"));
            $userName = $this->requestParameter("userName");
            $userId = 0;
            if($userName)
            {
            	//找userId
            	$lmcui = $this->get("library_model_clean_userinfo");
            	$userInfo = $lmcui->isExistUserName($userName);
            	if($userInfo)
            	{
            		$userId = $userInfo->getUserId();
            	}
            }
			$result = $lmcsm->getPageSystemMessage($pageIndex,$pageSize,$startDate,$endDate,$companyId,$userId,$type);
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


	public function addSystemMessageAction()
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
			
			return $this->render("CleanAdminBundle:SystemMessage:addSystemMessage.html.twig",array("companyInfo" => $companyInfo));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addSystemMessageSubmitAction()
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
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$messageType = intval($this->requestParameter("messageType"));
			if($messageType == 1)
			{
				$messageContent = $this->requestParameter("messageContent");
			}elseif($messageType == 2)
			{
				$content = $this->requestParameter("messageContent");
				if(!$content)
				{
					return new Response($this->getAPIResultJson("E02000", "请填写消息内容", ""));
				}
				//生成插入message_content
				$lmcmc = $this->get("library_model_clean_messagecontent");
				$messageContentEntity = new MessageContentEntity();
				$messageContentEntity->setContent($content);
				$id=$lmcmc->addEntity($messageContentEntity);
				$messageContent = ConfigHandler::getCommonConfig("host").":".ConfigHandler::getCommonConfig("port")."/api/getMessageContent?id=".$id;
			}else
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$fromUserId = $this->LoginUserId;
			$title = $this->requestParameter("messageTitle");

			if(!$fromUserId || !$messageContent || !$title)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$entity = new SystemMessageEntity();
			$entity->setCompanyId($companyId);
			$entity->setTitle($title);
			$entity->setMessageContent($messageContent);
			$entity->setMessageType($messageType);
			$entity->setFromUserId($fromUserId);
			$entity->setToUserId(0);
			$systemMessageId = $lmcsm->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editSystemMessageAction()
	{
		try
		{	
			// if($this->CompanyId != -1)
			// {
   //             return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
            
            $systemMessageId = intval($this->requestParameter("systemMessageId"));
            if($systemMessageId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$lmcc = $this->get("library_model_clean_company");
	        if($this->CompanyId == -1)
	        {
	            $companyInfo = $lmcc->getEntityList();
	        }else{
	            $result = $lmcc->getEntity($this->CompanyId);
	            $companyInfo = array();
	            $companyInfo[] = $result;
	        }
			$lmcsm = $this->get("library_model_clean_systemmessage");
			$entity = $lmcsm->getEntity($systemMessageId);
			$systemMessageInfo = new systemMessageResult();
			if($entity)
			{
				$companyId=$entity->getCompanyId();
				if($companyId == -1 || $companyId == 0)
				{
					$companyName = '通用';
				}else
				{
					$companyNameInfo = $lmcc->getEntity($companyId);
					$companyName = $companyNameInfo->getCompanyName();
				}
                $systemMessageInfo->setSystemMessageId($entity->getSystemMessageId());
                $systemMessageInfo->setCompanyId($entity->getCompanyId());
                $systemMessageInfo->setTitle($entity->getTitle());
                $systemMessageInfo->setCreateTime($entity->getCreateTime());
                $systemMessageInfo->setCompanyName($companyName);
                $systemMessageInfo->setMessageType($entity->getMessageType());
                if($entity->getMessageType() == 2)
                {
                	$contentArr = explode("=", $entity->getMessageContent());
                	$messageContentId = intval($contentArr[1]);
                	$lmcmc = $this->get("library_model_clean_messagecontent");
				    $messageContentEntity = $lmcmc->getEntity($messageContentId);
				    $messageContent = $messageContentEntity->getContent();
                }else
                {
                	$messageContent = $entity->getMessageContent();
                }
                $systemMessageInfo->setMessageContent($messageContent);
			}	
			return $this->render("CleanAdminBundle:SystemMessage:editSystemMessage.html.twig",array("companyInfo" => $companyInfo,"systemMessageInfo"=>$systemMessageInfo));
			
		}
		catch (\Exception $ex)
		{	
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editSystemMessageSubmitAction()
	{
		try
		{	
			if($this->CompanyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			} 
			$systemMessageId = intval($this->requestParameter("systemMessageId"));
            if($systemMessageId <= 0)
			{
				return new Response($this->getAPIResultJson("E02000", "数据错误", ""));
			}
			$companyId = intval($this->requestParameter("companyId"));
			if($companyId <= 0 && $companyId != -1)
			{
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcsm = $this->get("library_model_clean_systemmessage");
			$entity = $lmcsm->getEntity($systemMessageId);
			$messageType = intval($this->requestParameter("messageType"));
			$title = $this->requestParameter("title");
			if($messageType <= 0 || !$title)
			{
				return new Response($this->getAPIResultJson("E02000", "缺少参数", ""));
			}
			$entity->setTitle($title);
			//消息类型不能修改
			if($messageType == 1)
			{
				$messageContent = $this->requestParameter("messageContent");
				$entity->setMessageContent($messageContent);
			}elseif($messageType == 2)
			{
				$content = $this->requestParameter("messageContent");
				//修改message_content
				$contentArr = explode("=", $entity->getMessageContent());
                $messageContentId = intval($contentArr[1]);
				$lmcmc = $this->get("library_model_clean_messagecontent");
				$messageContentEntity = $lmcmc->getEntity($messageContentId);
				$messageContentEntity->setContent($content);
				$lmcmc->editEntity($messageContentEntity);
			}
			$lmcsm->editEntity($entity);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));
			
		}
		catch (\Exception $ex)
		{
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}


    public function deleteSystemMessageListAction()  
    {
        try
        {
   //          if($this->CompanyId != -1)
			// {
			// 	return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			// }
            
            $systemMessageIdList = $this->requestParameter("systemMessageIdList");
            if(!$systemMessageIdList)
            {
            	return new Response($this->getAPIResultJson("E02000", "请选择数据", ""));
            }
            $listArr = explode(",", $systemMessageIdList);
            $lmcsm = $this->get("library_model_clean_systemmessage");
            for($i=0;$i<count($listArr);$i++)
            {	
            	if($listArr[$i]>0)
            	{	
            		$entity = $lmcsm->getEntity($listArr[$i]);
            		if($entity->getMessageType() == 2)
            		{
            			//图文消息
            			$lmcmc = $this->get("library_model_clean_messagecontent");
            			$contentArr = explode("=", $entity->getMessageContent());
                		$messageContentId = intval($contentArr[1]);
                		$lmcmc->deleteEntity($messageContentId);
            		}
            		$lmcsm->deleteEntity($listArr[$i]);
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