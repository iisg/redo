<?php
namespace Repeka\Plugins\Redo\Authentication;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;

class PKUserDataUpdater {
    use CommandBusAware;

    /** @var PKAuthenticationClient */
    private $authenticationClient;
    /** @var UserDataMapping */
    private $userDataMapping;

    public function __construct(PKAuthenticationClient $authenticationClient, UserDataMapping $userDataMapping) {
        $this->authenticationClient = $authenticationClient;
        $this->userDataMapping = $userDataMapping;
    }

    public function updateUserData(User $user) {
        if ($this->userDataMapping->mappingExists()) {
            FirewallMiddleware::bypass($this->updateUserDataBasedOnMapping($user));
        }
    }

    public function updateUserDataBasedOnMapping(User $user) {
        return function () use ($user) {
            $userData = $this->authenticationClient->fetchUserData($user->getUsername());
            $importConfig = $this->userDataMapping->getImportConfig();
            $newContents = $this->handleCommand(new MetadataImportQuery($userData, $importConfig))->getAcceptedValues()->toArray();
            if (isset($newContents[SystemMetadata::USERNAME])) {
                unset($newContents[SystemMetadata::USERNAME]);
            }
            $currentContents = $user->getUserData()->getContents()->toArray();
            $updatedContents = ResourceContents::fromArray(array_replace($currentContents, $newContents));
            $this->handleCommand(new ResourceUpdateContentsCommand($user->getUserData(), $updatedContents));
        };
    }
}
