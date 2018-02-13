<?php
namespace Repeka\Application\Controller\Site;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller {
    /**
     * @Route("/admin{suffix}", requirements={"suffix"=".*"}, methods={"GET"})
     * @Template
     */
    public function adminAction() {
    }
}
