<?php
namespace Repeka\UserModule\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller {
    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function formAction() {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse('/');
        }
        $authenticationUtils = $this->get('security.authentication_utils');
        return [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ];
    }

    /**
     * @Route("/api/test")
     */
    public function testAction() {
        return new Response("AAA");
    }
}
