<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class ResourceKindUpdateCommandHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository, ResourceRepository $resourceRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceKindUpdateCommand $command): ResourceKind {
        $resourceKind = $command->getResourceKind();
        $resourceKind->update($command->getLabel(), $command->getDisplayStrategies());
        $resourceKind->setMetadataList($command->getMetadataList());
        $this->updateWorkflow($command->getWorkflow(), $resourceKind);
        return $this->resourceKindRepository->save($resourceKind);
    }

    private function updateWorkflow(?ResourceWorkflow $workflow, ResourceKind $resourceKind): void {
        if ($workflow && !$resourceKind->getWorkflow()) {
            $resourceKind->setWorkflow($workflow);
            $resources = $this->resourceRepository->findByQuery(ResourceListQuery::builder()->filterByResourceKind($resourceKind)->build());
            $firstTransition = SystemTransition::CREATE();
            foreach ($resources as $resource) {
                $firstTransition->apply($resource);
                $this->resourceRepository->save($resource);
            }
        }
    }
}
