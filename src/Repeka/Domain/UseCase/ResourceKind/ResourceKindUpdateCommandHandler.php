<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\ResourceKindFactory;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindUpdateCommandHandler {
    /** @var ResourceKindFactory */
    private $resourceKindFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindFactory $resourceKindFactory, ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindFactory = $resourceKindFactory;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function handle(ResourceKindUpdateCommand $command): ResourceKind {
        $resourceKind = $command->getResourceKind();
        $newMetadataList = $this->resourceKindFactory->createMetadataList($resourceKind, $command->getMetadataList());
        $resourceKind->update($command->getLabel(), $newMetadataList, $command->getDisplayStrategies());
        return $this->resourceKindRepository->save($resourceKind);
    }
}
