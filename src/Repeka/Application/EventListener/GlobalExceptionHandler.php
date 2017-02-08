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

    public function createErrorResponse($e) {
        if (!$e instanceof DomainException) {
            return new JsonResponse([
                'status' => 500,
                'message' => $this->isDebug ? $e->getMessage() : 'Internal server error, please try again later'
            ]);
        } else {
            /* @var DomainException $e */
            return new JsonResponse([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => $e->getData()
            ]);
        }
    }

    public static function getSubscribedEvents() {
        return [KernelEvents::EXCEPTION => 'onException'];
    }
}
