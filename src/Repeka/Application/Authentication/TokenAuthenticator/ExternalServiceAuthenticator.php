<?php
namespace Repeka\Application\Authentication\TokenAuthenticator;

use Assert\Assertion;
use Repeka\Application\Authentication\PKAuthenticationClient;
use Repeka\Application\Authentication\PKAuthenticationException;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\XmlImport\XmlImportQuery;
use Repeka\Domain\XmlImport\Executor\ImportResult;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExternalServiceAuthenticator extends TokenAuthenticator {
    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var CommandBus */
    private $commandBus;
    private $mappingConfigPath;

    public function __construct(PKAuthenticationClient $authenticationClient, CommandBus $commandBus, string $mappingConfigPath) {
        $this->authenticationClient = $authenticationClient;
        $this->commandBus = $commandBus;
        $this->mappingConfigPath = $mappingConfigPath;
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
            /** @var $user UserEntity */
            $user = $userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            try {
                $user = $this->commandBus->handle(new UserCreateCommand($username));
            } catch (\Exception $e) {
                throw new CustomUserMessageAuthenticationException('Invalid username or password', [], 0, $e);
            }
        }
        if ($this->mappingConfigPath && is_readable($this->mappingConfigPath)) {
            $this->updateUserData($username, $user);
        }
        return $this->createAuthenticatedToken($user, $providerKey);
    }

    private function updateUserData(string $username, UserEntity $user) {
        $userData = $this->authenticationClient->fetchUserData($username);
        $fetchedValues = $this->mapUserData($userData);
        $acceptedValues = $fetchedValues->getAcceptedValues();
        $userResource = $user->getUserData();
        $this->commandBus->handle(new ResourceUpdateContentsCommand($userResource, $acceptedValues));
    }

    private function mapUserData(array $userData): ImportResult {
        $jsonConfig = json_decode(file_get_contents($this->mappingConfigPath), true);
        Assertion::notNull($jsonConfig, 'Invalid user data mapping in ' . $this->mappingConfigPath . ': ' . json_last_error_msg());
        $userResourceKind = $this->commandBus->handle(new ResourceKindQuery(SystemResourceKind::USER));
        $xmlEncoder = new XmlEncoder();
        $xml = $xmlEncoder->encode($userData, 'xml');
        return $this->commandBus->handle(new XmlImportQuery($xml, $jsonConfig, $userResourceKind));
    }
}
