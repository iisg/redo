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
        $resourceKind = $this->resourceKindRepository->findOne($command->getResourceKindId());
        $newMetadataList = $this->resourceKindFactory->createMetadataList($resourceKind, $command->getMetadataList());
        $resourceKind->update($command->getLabel(), $newMetadataList);
        return $this->resourceKindRepository->save($resourceKind);
    }
}
