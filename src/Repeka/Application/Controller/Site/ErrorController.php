<?php
namespace Repeka\Application\Controller\Site;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorController extends Controller {
    /**
     * @Route("/403")
     * @Template
     */
    public function error403Action() {
    }
}
