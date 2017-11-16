<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Repeka\Application\Authentication\PKAuthenticationClient;
use Repeka\Application\Authentication\PKAuthenticationException;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ExternalServiceAuthenticator extends TokenAuthenticator {
    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(PKAuthenticationClient $authenticationClient, CommandBus $commandBus) {
        $this->authenticationClient = $authenticationClient;
        $this->commandBus = $commandBus;
    }

    public function canAuthenticate(TokenInterface $token): bool {
        try {
            return $this->authenticationClient->authenticate($token->getUsername(), $token->getCredentials());
        } catch (PKAuthenticationException $e) {
            // external service's fault - throw it, we want to have it in logs
            throw $e;
        } catch (\Exception $e) {
            // local problem or we have detected invalid data before calling auth service (eg. login too short)
            return false;
        }
    }

    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface {
        $username = $token->getUsername();
        try {
            $user = $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            try {
                $user = $this->commandBus->handle(new UserCreateCommand($username));
            } catch (\Exception $e) {
                throw new CustomUserMessageAuthenticationException('Invalid username or password', [], 0, $e);
            }
        }
        return $this->createAuthenticatedToken($user, $providerKey);
    }
}
