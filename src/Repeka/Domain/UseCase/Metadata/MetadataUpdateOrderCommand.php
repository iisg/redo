<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\ResourceClassAwareCommand;

class MetadataUpdateOrderCommand extends ResourceClassAwareCommand {
    /** @var int[] */
    private $metadataIdsInOrder;

    public function __construct(array $metadataIdsInOrder, string $resourceClass) {
        parent::__construct($resourceClass);
        $this->metadataIdsInOrder = $metadataIdsInOrder;
    }

    public function getMetadataIdsInOrder(): array {
        return $this->metadataIdsInOrder;
    }
}
