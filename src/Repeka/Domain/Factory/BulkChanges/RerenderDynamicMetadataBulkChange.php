<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\EntityUtils;

class RerenderDynamicMetadataBulkChange extends BulkChange {
    public function createForChange(array $changeConfig): BulkChange {
        return new self();
    }

    protected function getChangeConfig(): array {
        return [];
    }

    public function apply(ResourceEntity $resource): ResourceEntity {
        EntityUtils::forceSetField($resource, true, 'displayStrategiesDirty');
        return $resource;
    }
}
