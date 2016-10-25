<?php
namespace Repeka\UserModule\Bundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtTokenCookieSetter extends ContainerAwareEventDispatcher {
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event) {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }
        $tokenCookie = $this->createTokenCookie($data['token']);
        $event->getResponse()->headers->setCookie($tokenCookie);
    }

    public function createTokenCookie(string $token): Cookie {
        $cookieName = $this->getContainer()->getParameter('jwt_cookie_name');
        return new Cookie($cookieName, $token);
    }
}
