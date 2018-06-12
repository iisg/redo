<?php
namespace Repeka\Application\Authentication;

use Repeka\Application\Authentication\TokenAuthenticator\ExternalServiceAuthenticator;
use Repeka\Application\Authentication\TokenAuthenticator\LocalDatabaseAuthenticator;
use Repeka\Application\Authentication\TokenAuthenticator\TokenAuthenticator;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\UseCase\User\UserGrantRolesCommand;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

/**
 * @see https://symfony.com/doc/current/security/custom_password_authenticator.html#how-it-works
 */
class PKAuthenticator implements SimpleFormAuthenticatorInterface {
    use CommandBusAware;

    /** @var TokenAuthenticator[] */
    private $authenticators;
    /** @var UserLoaderInterface */
    private $userLoader;

    public function __construct(
        ExternalServiceAuthenticator $externalServiceAuthenticator,
        LocalDatabaseAuthenticator $localDatabaseAuthenticator,
        UserLoaderInterface $useLoader
    ) {
        $this->authenticators = [$externalServiceAuthenticator, $localDatabaseAuthenticator];
        $this->userLoader = $useLoader;
    }

    /** @inheritdoc */
    public function createToken(Request $request, $username, $password, $providerKey): TokenInterface {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    /** @inheritdoc */
    private function createAuthenticatedToken(string $username, $providerKey): TokenInterface {
        return FirewallMiddleware::bypass(
            function () use ($providerKey, $username) {
                $user = $this->userLoader->loadUserByUsername($username);
                $this->handleCommand(new UserGrantRolesCommand($user));
                return new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
            }
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey): bool {
        return ($token instanceof UsernamePasswordToken) && ($token->getProviderKey() === $providerKey);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->canAuthenticate($token)) {
                $username = $authenticator->authenticate($token, $userProvider);
                return $this->createAuthenticatedToken($username, $providerKey);
            }
        }
        throw new CustomUserMessageAuthenticationException('Authentication failed');
    }
}
