<?php
namespace Repeka\Website\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {
    /**
     * @Route("/", name="home")
     * @Template
     */
    public function homeAction() {
    }
}
