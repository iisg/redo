<?php
namespace Repeka\Domain\Validation\Strippers;

use Repeka\Domain\Entity\Metadata;

class UnknownMetadataGroupStripper {
    private $metadataGroupIds;

    public function __construct(array $metadataGroups) {
        $this->metadataGroupIds = array_column($metadataGroups, 'id');
    }

    public function getSupportedMetadataGroup($groupId): string {
        return in_array($groupId, $this->metadataGroupIds) ? $groupId : Metadata::DEFAULT_GROUP;
    }
}
