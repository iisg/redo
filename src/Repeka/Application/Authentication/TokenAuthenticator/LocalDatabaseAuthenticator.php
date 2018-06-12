<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LocalDatabaseAuthenticator extends TokenAuthenticator {
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;
    /** @var bool */
    private $localAccountsEnabled;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, bool $localAccountsEnabled) {
        $this->passwordEncoder = $passwordEncoder;
        $this->localAccountsEnabled = $localAccountsEnabled;
    }

    public function canAuthenticate(TokenInterface $token): bool {
        return $this->localAccountsEnabled;
    }

    public function authenticate(TokenInterface $token, UserProviderInterface $userProvider): string {
        $currentUserEntity = $this->getUserOrThrow($token->getUsername(), $userProvider);
        if ($this->checkPasswordLocally($currentUserEntity, $token->getCredentials())) {
            $this->authenticateExistingUser($token->getUsername(), $token->getCredentials(), $userProvider);
            return $token->getUsername();
        } else {
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
        }
    }

    private function getUserOrThrow(string $username, UserProviderInterface $userProvider): UserInterface {
        try {
            return $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid username', [], 0, $e);
        }
    }

    private function checkPasswordLocally(UserInterface $user, string $password): bool {
        return $this->passwordEncoder->isPasswordValid($user, $password);
    }

    private function authenticateExistingUser(string $username, string $password, UserProviderInterface $userProvider): void {
        try {
            $user = $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid username', [], 0, $e);
        }
        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
        }
    }
}
