<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class MetadataUpdateOrderCommand extends NonValidatedCommand {
    /** @var int[] */
    private $metadataIdsInOrder;

    public function __construct(array $metadataIdsInOrder) {
        $this->metadataIdsInOrder = $metadataIdsInOrder;
    }

    public function getMetadataIdsInOrder(): array {
        return $this->metadataIdsInOrder;
    }
}
