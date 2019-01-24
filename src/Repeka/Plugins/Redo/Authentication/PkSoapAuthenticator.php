<?php
namespace Repeka\Plugins\Redo\Authentication;

use Repeka\Application\Authentication\TokenAuthenticator;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserCreateCommandAdjuster;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PkSoapAuthenticator extends TokenAuthenticator {
    /** @var bool */
    private $enabled;
    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var CommandBus */
    private $commandBus;
    /** @var PKUserDataUpdater */
    private $userDataUpdater;

    public function __construct(
        bool $enabled,
        PKAuthenticationClient $authenticationClient,
        CommandBus $commandBus,
        PKUserDataUpdater $userDataUpdater
    ) {
        $this->enabled = $enabled;
        $this->authenticationClient = $authenticationClient;
        $this->commandBus = $commandBus;
        $this->userDataUpdater = $userDataUpdater;
    }

    public function canAuthenticate(TokenInterface $token): bool {
        if (!$this->enabled) {
            return false;
        }
        try {
            return $this->authenticationClient->authenticate(self::normalizeUsername($token->getUsername()), $token->getCredentials());
        } catch (PKAuthenticationException $e) {
            // external service's fault - throw it, we want to have it in logs
            throw $e;
        } catch (\Exception $e) {
            // local problem or we have detected invalid data before calling auth service (eg. login too short)
            return false;
        }
    }

    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider): string {
        $username = self::normalizeUsername($token->getUsername());
        try {
            /** @var $user UserEntity */
            $user = $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            try {
                $user = FirewallMiddleware::bypass(
                    function () use ($username) {
                        return $this->commandBus->handle(new UserCreateCommand($username));
                    }
                );
            } catch (\Exception $e) {
                throw new CustomUserMessageAuthenticationException('Invalid username or password', [], 0, $e);
            }
        }
        $this->userDataUpdater->updateUserData($user);
        return $username;
    }

    /**
     * Add b/ prefix to all only-numeric usernames to support PK SOAP service authentication without inputting it.
     * Make the username lowercase.
     */
    public static function normalizeUsername(string $username) {
        $normalizedUsername = UserCreateCommandAdjuster::normalizeUsername($username);
        if (is_numeric($normalizedUsername) && strlen($normalizedUsername) == 6) {
            $normalizedUsername = 'b/' . $normalizedUsername;
        }
        return $normalizedUsername;
    }
}
