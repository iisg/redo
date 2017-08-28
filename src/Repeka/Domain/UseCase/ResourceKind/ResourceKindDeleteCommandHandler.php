<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindDeleteCommandHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function handle(ResourceKindDeleteCommand $command): void {
        $this->resourceKindRepository->delete($command->getResourceKind());
    }
}
