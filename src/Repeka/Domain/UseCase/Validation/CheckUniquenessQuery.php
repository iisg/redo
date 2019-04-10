<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;

class CheckUniquenessQuery extends AbstractCommand {
    use RequireNoRoles;

    private $metadataId;
    private $resourceClass;
    private $metadataValue;
    private $resourceId;

    public function __construct($metadataId, $resourceClass, $metadataValue, $resourceId) {
        $this->metadataId = $metadataId;
        $this->resourceClass = $resourceClass;
        $this->metadataValue = $metadataValue;
        $this->resourceId = $resourceId;
    }

    public function getMetadataId() {
        return $this->metadataId;
    }

    public function getResourceId() {
        return $this->resourceId;
    }

    public function getResourceClass() {
        return $this->resourceClass;
    }

    public function getMetadataValue() {
        return $this->metadataValue;
    }

    public static function fromArray(array $array) {
        return new CheckUniquenessQuery(
            $array['metadataId'],
            $array['resourceClass'],
            $array['metadataValue'],
            $array['resourceId'] ?? null
        );
    }
}
