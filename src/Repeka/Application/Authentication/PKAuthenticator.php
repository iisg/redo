<?php
namespace Repeka\Application\Authentication;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

class PKAuthenticator implements SimpleFormAuthenticatorInterface {
    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var CommandBus */
    private $commandBus;
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;
    /** @var bool */
    private $localAccountsEnabled;

    public function __construct(
        PKAuthenticationClient $authenticationClient,
        CommandBus $commandBus,
        UserPasswordEncoderInterface $passwordEncoder,
        bool $localAccountsEnabled
    ) {
        $this->authenticationClient = $authenticationClient;
        $this->commandBus = $commandBus;
        $this->passwordEncoder = $passwordEncoder;
        $this->localAccountsEnabled = $localAccountsEnabled;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        if (!$this->authenticateRemotely($token->getUsername(), $token->getCredentials())
            && !$this->authenticateLocally($token->getUsername(), $token->getCredentials(), $userProvider)
        ) {
            throw new CustomUserMessageAuthenticationException('Authentication failed');
        }
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            try {
                $user = $this->commandBus->handle(new UserCreateCommand($token->getUsername()));
            } catch (\Exception $e) {
                throw new CustomUserMessageAuthenticationException('Invalid username or password', [], 0, $e);
            }
        }
        return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
    }

    public function supportsToken(TokenInterface $token, $providerKey) {
        return ($token instanceof UsernamePasswordToken) && ($token->getProviderKey() === $providerKey) && $token->getCredentials();
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function createToken(Request $request, $username, $password, $providerKey) {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    private function authenticateRemotely(string $username, string $password) {
        try {
            return $this->authenticationClient->authenticate($username, $password);
        } catch (PKAuthenticationException $e) {
            // external service's fault - throw it, we want to have it in logs
            throw $e;
        } catch (\Exception $e) {
            // local problem or we have detected invalid data before calling auth service (eg. login too short)
            return false;
        }
    }

    private function authenticateLocally(string $username, string $password, UserProviderInterface $userProvider) {
        if (!$this->localAccountsEnabled) {
            return false;
        }
        try {
            $user = $userProvider->loadUserByUsername($username);
            return $this->passwordEncoder->isPasswordValid($user, $password);
        } catch (UsernameNotFoundException $e) {
            return false;
        }
    }
}
