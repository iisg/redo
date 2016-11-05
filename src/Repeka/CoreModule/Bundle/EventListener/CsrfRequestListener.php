<?php
namespace Repeka\CoreModule\Bundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class CsrfRequestListener {
    const TOKEN_HEADER = 'X-CSRF-Token';

    /**
     * @var CsrfTokenManager
     */
    private $tokenManager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * CsrfRequestListener constructor.
     */
    public function __construct(CsrfTokenManager $tokenManager, TokenStorageInterface $tokenStorage) {
        $this->tokenManager = $tokenManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        if (!$request->isMethodSafe() && $this->isApi($request)) {
            $token = new CsrfToken(self::class, $request->headers->get(self::TOKEN_HEADER));
            if (!$this->tokenManager->isTokenValid($token)) {
                $event->setResponse(new Response('Invalid CSRF Token.', 400));
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        $tokenToSet = null;
        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($this->isApi($request) && $request->isXmlHttpRequest() && $response && $response->isSuccessful()) {
            $tokenToSet = $this->tokenManager->getToken(self::class);
            if (!$request->isMethodSafe()) {
                $tokenToSet = $this->tokenManager->refreshToken(self::class);
            }
            $response->headers->set(self::TOKEN_HEADER, $tokenToSet->getValue());
        }
    }

    private function isApi(Request $request) {
        return strpos($request->getPathInfo(), '/api/') === 0;
    }
}
