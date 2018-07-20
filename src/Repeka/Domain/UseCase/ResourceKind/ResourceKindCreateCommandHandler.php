<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindCreateCommandHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function handle(ResourceKindCreateCommand $command): ResourceKind {
        $resourceKind = new ResourceKind(
            $command->getLabel(),
            $command->getMetadataList(),
            $command->getWorkflow()
        );
        return $this->resourceKindRepository->save($resourceKind);
    }
}
