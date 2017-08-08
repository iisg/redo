<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;

class ResourceDeleteCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceDeleteCommand $command): void {
        $this->resourceRepository->delete($command->getResource());
    }
}
