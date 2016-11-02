<?php
namespace Repeka\CoreModule\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AboutController extends Controller {
    /**
     * @Route("/about")
     * @Template
     */
    public function aboutAction() {
    }
}
