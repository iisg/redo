<?php
namespace Repeka\Application\Authentication;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\User;
use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;

class PKUserDataUpdater {
    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var CommandBus */
    private $commandBus;
    /** @var ImportConfigFactory */
    private $importConfigFactory;
    /** @var string */
    private $mappingConfigPath;

    public function __construct(
        PKAuthenticationClient $authenticationClient,
        CommandBus $commandBus,
        ImportConfigFactory $importConfigFactory,
        string $mappingConfigPath
    ) {
        $this->importConfigFactory = $importConfigFactory;
        $this->mappingConfigPath = $mappingConfigPath;
        $this->authenticationClient = $authenticationClient;
        $this->commandBus = $commandBus;
    }

    public function updateUserData(User $user) {
        if ($this->mappingConfigPath && is_readable($this->mappingConfigPath)) {
            $userData = $this->authenticationClient->fetchUserData($user->getUsername());
            $newContents = $this->mapUserData($userData)->toArray();
            if (isset($newContents[SystemMetadata::USERNAME])) {
                unset($newContents[SystemMetadata::USERNAME]);
            }
            $currentContents = $user->getUserData()->getContents()->toArray();
            $updatedContents = ResourceContents::fromArray(array_replace($currentContents, $newContents));
            $this->commandBus->handle(new ResourceUpdateContentsCommand($user->getUserData(), $updatedContents));
        }
    }

    private function mapUserData(array $userData): ResourceContents {
        $userResourceKind = $this->commandBus->handle(new ResourceKindQuery(SystemResourceKind::USER));
        $importConfig = $this->importConfigFactory->fromFile($this->mappingConfigPath, $userResourceKind);
        return $this->commandBus->handle(new MetadataImportQuery($userData, $importConfig))->getAcceptedValues();
    }
}
