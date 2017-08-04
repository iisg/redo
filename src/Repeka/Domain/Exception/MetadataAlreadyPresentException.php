<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;

class MetadataAlreadyPresentException extends DomainException {
    public function __construct(ResourceKind $resourceKind, Metadata $metadata) {
        parent::__construct('metadataAlreadyPresent', 409, [
            'resourceKindId' => $resourceKind->getId(),
            'metadataId' => $metadata->getId(),
        ]);
    }
}
