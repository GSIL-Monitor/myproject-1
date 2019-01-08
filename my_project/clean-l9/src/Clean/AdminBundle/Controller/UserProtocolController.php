<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;
use Clean\LibraryBundle\Entity\BasicInfoEntity;
use Symfony\Component\HttpFoundation\Response;

class UserProtocolController extends BaseController {

	public function editUserProtocolAction() {
		try
		{
			$language = $this->requestParameter("language");
			if (!$language) {
				return new Response($this->getAPIResultJson("E02000", "请选择语言", ""));
			}
			$lmcbi = $this->get("library_model_clean_basicinfo");
			$basicInfo = $lmcbi->getBasicInfoByTypeAndCompanyId(3, 15, $language);
			$data = $basicInfo ? $basicInfo : array();
			$lmcc = $this->get("library_model_clean_company");

			return $this->render("CleanAdminBundle:BasicInfo:editUserProtocol.html.twig", array("basicInfo" => $data));
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function editUserProtocolSubmitAction() {
		try
		{
			$content = $this->requestParameter("content");
			$lang = $this->requestParameter("lang");
			$basicInfoId = intval($this->requestParameter("basicInfoId"));
			$description = $this->requestParameter("description");
			if ($basicInfoId <= 0) {
				return new Response($this->getAPIResultJson("E02000", "权限验证失败", ""));
			}

			$lmcbi = $this->get("library_model_clean_basicinfo");
			$basicInfo = $lmcbi->getEntity($basicInfoId);
			if (!$basicInfo) {
				return new Response($this->getAPIResultJson("E02000", "数据异常", ""));
			}
			if (!empty($content)) {
				$basicInfo->setContent($content);
			}
			if (!empty($lang)) {
				$basicInfo->setLang($lang);
			}
			if (!empty($description)) {
				$basicInfo->setDescription($description);
			}

			$basicInfo = $lmcbi->editEntity($basicInfo);
			return new Response($this->getAPIResultJson("N00000", "数据修改成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addUserProtocolAction() {
		try
		{
			return $this->render("CleanAdminBundle:BasicInfo:addUserProtocol.html.twig", array());
		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

	public function addUserProtocolSubmitAction() {
		try
		{

			$content = $this->requestParameter("content");
			$lang = $this->requestParameter("lang");
			$companyId = 15;
			$type = 3;
			$description = $this->requestParameter("description");
			if ($companyId <= 0 || $type <= 0 || !$lang || !$content) {
				return new Response($this->getAPIResultJson("E02000", "确实数据", ""));
			}
			$lmcbi = $this->get("library_model_clean_basicinfo");

			$entity = new BasicInfoEntity();
			$entity->setCompanyId($companyId);
			$entity->setType($type);
			$entity->setDescription($description);
			$entity->setLang($lang);
			$entity->setContent($content);

			$basicInfo = $lmcbi->addEntity($entity);

			return new Response($this->getAPIResultJson("N00000", "添加成功", ""));

		} catch (\Exception $ex) {
			return new Response($this->getAPIResultJson("E01000", "服务器异常", ""));
		}
	}

}
?>