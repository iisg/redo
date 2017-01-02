<?php
namespace Repeka\Application\EventListener;

use Repeka\Application\Entity\UserEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Displays JSON error instead of redirecting to login form if the request is asynchronous.
 *
 * @see http://intelligentbee.com/blog/2015/08/25/how-to-fix-symfony2-ajax-login-redirect/
 */
class AjaxAuthenticationListener {
    use TargetPathTrait;

    const FIREWALL_NAME = 'main';
    const ADMIN_PANEL_PREFIX = '/admin/';

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(TokenStorage $tokenStorage, SessionInterface $session) {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function onCoreException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
                $event->setResponse($this->buildResponseForXmlHttpRequest($request));
            }
        }
    }

    private function buildResponseForXmlHttpRequest(Request $request) {
        if ($this->tokenStorage->getToken()->getUser() instanceof UserEntity) {
            return $this->buildAccessDeniedResponse();
        } else {
            $this->saveTargetUrlIfFromAdminPanel($request);
            return $this->buildAuthenticationMissingResponse();
        }
    }

    private function buildAccessDeniedResponse() {
        return new JsonResponse([
            'status' => 403,
            'message' => 'Forbidden',
        ], 403);
    }

    private function buildAuthenticationMissingResponse() {
        return new JsonResponse([
            'status' => 401,
            'message' => 'Unauthorized',
        ], 401);
    }

    private function saveTargetUrlIfFromAdminPanel(Request $request) {
        $callingAddress = $request->headers->get('referer');
        if ($offset = strpos($callingAddress, self::ADMIN_PANEL_PREFIX)) {
            $path = substr($callingAddress, $offset);
            $this->saveTargetPath($this->session, self::FIREWALL_NAME, $path);
        }
    }
}
