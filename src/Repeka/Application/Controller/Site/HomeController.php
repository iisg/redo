<?php
namespace Repeka\Application\Controller\Site;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {
    /**
     * @Route("/")
     * @Template
     */
    public function homeAction() {
    }
}
