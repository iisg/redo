<?php
namespace Repeka\Application\Authentication;

use Repeka\Application\Authentication\TokenAuthenticator\ExternalServiceAuthenticator;
use Repeka\Application\Authentication\TokenAuthenticator\LocalDatabaseAuthenticator;
use Repeka\Application\Authentication\TokenAuthenticator\Reauthenticator;
use Repeka\Application\Authentication\TokenAuthenticator\TokenAuthenticator;
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
    /** @var TokenAuthenticator[] */
    private $authenticators;

    public function __construct(
        Reauthenticator $reauthenticator,
        ExternalServiceAuthenticator $externalServiceAuthenticator,
        LocalDatabaseAuthenticator $localDatabaseAuthenticator
    ) {
        $this->authenticators = [$reauthenticator, $externalServiceAuthenticator, $localDatabaseAuthenticator];
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function createToken(Request $request, $username, $password, $providerKey): TokenInterface {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    public function supportsToken(TokenInterface $token, $providerKey): bool {
        return ($token instanceof UsernamePasswordToken) && ($token->getProviderKey() === $providerKey);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): TokenInterface {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->canAuthenticate($token)) {
                return $authenticator->authenticate($token, $userProvider, $providerKey);
            }
        }
        throw new CustomUserMessageAuthenticationException('Authentication failed');
    }
}
