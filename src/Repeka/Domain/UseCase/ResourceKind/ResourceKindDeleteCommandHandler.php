<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindDeleteCommandHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        ResourceKindRepository $resourceKindRepository,
        MetadataRepository $metadataRepository
    ) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(ResourceKindDeleteCommand $command): void {
        $resourceKind = $command->getResourceKind();
        $this->resourceKindRepository->removeEveryResourceKindsUsageInOtherResourceKinds($resourceKind);
        $this->metadataRepository->removeResourceKindFromMetadataConstraints($resourceKind);
        $this->resourceKindRepository->delete($resourceKind);
    }
}
