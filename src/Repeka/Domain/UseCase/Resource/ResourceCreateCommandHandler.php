<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceCreateCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), $command->getContents());
        return $this->resourceRepository->save($resource);
    }
}
