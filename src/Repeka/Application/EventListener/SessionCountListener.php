<?php
namespace Repeka\Application\EventListener;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SessionCountListener {

    use CommandBusAware;

    private const ACKNOWLEDGED = 'acknowledged';

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        $session = $request->getSession();
        if ($session && !$session->get(self::ACKNOWLEDGED)) {
            $session->set(self::ACKNOWLEDGED, true);
            $this->handleCommand(new EventLogCreateCommand($request, 'sessions'));
        }
    }
}
