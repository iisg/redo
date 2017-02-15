<?php
namespace Repeka\Application\EventListener;

use Repeka\Domain\Exception\DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class GlobalExceptionHandler implements EventSubscriberInterface {
    private $isDebug;

    public function __construct($isDebug) {
        $this->isDebug = $isDebug;
    }

    public function onException(GetResponseForExceptionEvent $event) {
        $errorResponse = $this->createErrorResponse($event->getException());
        $event->setResponse($errorResponse);
    }

    public function createErrorResponse(\Exception $e) {
        if (!$e instanceof DomainException) {
            return new JsonResponse([
                'message' => $this->isDebug ? $e->getMessage() : 'Internal server error, please try again later'
            ], 500);
        } else {
            /* @var DomainException $e */
            return new JsonResponse([
                'message' => $e->getMessage(),
                'data' => $e->getData()
            ], $e->getCode());
        }
    }

    public static function getSubscribedEvents() {
        return [KernelEvents::EXCEPTION => 'onException'];
    }
}
