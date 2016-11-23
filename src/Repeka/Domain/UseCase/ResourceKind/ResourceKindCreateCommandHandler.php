<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\ResourceKindFactory;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindCreateCommandHandler {
    /** @var ResourceKindFactory */
    private $resourceKindFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindFactory $resourceKindFactory, ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindFactory = $resourceKindFactory;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function handle(ResourceKindCreateCommand $command): ResourceKind {
        $resourceKind = $this->resourceKindFactory->create($command);
        return $this->resourceKindRepository->save($resourceKind);
    }
}
