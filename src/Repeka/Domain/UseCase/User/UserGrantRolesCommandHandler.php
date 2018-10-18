<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class UserGrantRolesCommandHandler {
    /** @var UserRepository */
    private $userRepository;
    /** @var array */
    private $resourceClassesConfig;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        array $resourceClassesConfig,
        UserRepository $userRepository,
        ResourceRepository $resourceRepository,
        MetadataRepository $metadataRepository
    ) {
        $this->resourceClassesConfig = $resourceClassesConfig;
        $this->userRepository = $userRepository;
        $this->resourceRepository = $resourceRepository;
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(UserGrantRolesCommand $command): void {
        $user = $command->getUser();
        $roles = [];
        foreach ($this->resourceClassesConfig as $resourceClass => $resourceClassConfig) {
            foreach (SystemRole::values() as $possibleRoleToGrant) {
                foreach ($resourceClassConfig[$possibleRoleToGrant->getConfigKey()] as $contentsFilter) {
                    try {
                        $contentsFilter = ResourceContents::fromArray($contentsFilter)
                            ->withMetadataNamesMappedToIds($this->metadataRepository);
                    } catch (EntityNotFoundException $exception) {
                        // TODO log this when we have logger, invalid config!
                        continue;
                    }
                    $query = ResourceListQuery::builder()
                        ->filterByIds([$user->getUserData()->getId()])
                        ->filterByContents($contentsFilter)
                        ->setResultsPerPage(1)
                        ->setPage(1)
                        ->build();
                    $results = $this->resourceRepository->findByQuery($query);
                    if ($results->count() === 1) {
                        $rolesToGrant = array_merge([$possibleRoleToGrant], $possibleRoleToGrant->getImpliedRoles());
                        foreach ($rolesToGrant as $role) {
                            $roles[] = $role->roleName();
                            $roles[] = $role->roleName($resourceClass);
                        }
                    }
                }
            }
        }
        $user->updateRoles(array_values(array_unique($roles)));
        $this->userRepository->save($user);
    }
}
