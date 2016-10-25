<?php
namespace Repeka\UserModule\Bundle\Controller;

use Repeka\UserModule\Bundle\EventListener\JwtTokenCookieSetter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route(service="repeka.core.logout_controller")
 */
class LogoutController extends Controller {
    /**
     * @var JwtTokenCookieSetter
     */
    private $cookieSetter;

    public function __construct(JwtTokenCookieSetter $cookieSetter) {
        $this->cookieSetter = $cookieSetter;
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction() {
        $response = new RedirectResponse('/');
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $cookie = $this->cookieSetter->createTokenCookie('');
            $response->headers->setCookie($cookie);
        }
        return $response;
    }
}
