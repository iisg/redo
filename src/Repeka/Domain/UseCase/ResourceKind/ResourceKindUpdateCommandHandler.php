<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindUpdateCommandHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function handle(ResourceKindUpdateCommand $command): ResourceKind {
        $resourceKind = $command->getResourceKind();
        $resourceKind->update($command->getLabel(), $command->getDisplayStrategies());
        $resourceKind->setMetadataList($command->getMetadataList());
        return $this->resourceKindRepository->save($resourceKind);
    }
}
