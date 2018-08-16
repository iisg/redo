<?php
namespace Repeka\Application\EventListener;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\UseCase\User\UserAuthenticateCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationAuditListener {
    use CommandBusAware;
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $this->handleCommand(new UserAuthenticateCommand($event->getAuthenticationToken()->getUsername(), $this->container));
    }

    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event) {
        try {
            $this->handleCommand(new UserAuthenticateCommand($event->getAuthenticationToken()->getUsername(), $this->container, false));
        } catch (\DomainException $e) {
            // the failure has been audited by the UserAuthenticateCommandAuditor
        }
    }
}
