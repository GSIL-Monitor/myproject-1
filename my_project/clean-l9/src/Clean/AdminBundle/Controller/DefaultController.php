<?php

namespace Clean\AdminBundle\Controller;

use Clean\AdminBundle\Controller\BaseController;

class DefaultController extends BaseController {

	public function indexAction() {
		if ($this->CompanyId == -1) {
			$isAdmin = true;
		} else {
			$isAdmin = false;
		}
		return $this->render("CleanAdminBundle:Default:index.html.twig", array(
			"userName" => $this->UserName,
			"isAdmin" => $isAdmin,
		));
	}

}
?>