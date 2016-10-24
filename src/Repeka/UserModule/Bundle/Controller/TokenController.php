<?php
namespace Repeka\UserModule\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TokenController extends Controller {
    /**
     * @Route("/api/token")
     */
    public function generateTokenAction() {
        // The security layer will intercept this request
        return new Response('', 401);
    }
}
