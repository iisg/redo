<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;

class MetadataUpdateOrderCommand extends AbstractCommand {
    /** @var int[] */
    private $metadataIdsInOrder;
    /** @var string */
    private $resourceClass;

    public function __construct(array $metadataIdsInOrder, string $resourceClass) {
        $this->metadataIdsInOrder = $metadataIdsInOrder;
        $this->resourceClass = $resourceClass;
    }

    public function getMetadataIdsInOrder(): array {
        return $this->metadataIdsInOrder;
    }

    public function getResourceClass() {
        return $this->resourceClass;
    }
}
