<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\EntityUtils;

class MoveToPlaceBulkChange extends BulkChange {
    /** @var string $placeId */
    private $placeId;

    public function __construct(array $changeConfig = []) {
        $this->placeId = $changeConfig['placeId'] ?? null;
    }

    public function createForChange(array $changeConfig): BulkChange {
        return new self($changeConfig);
    }

    public function getChangeConfig(): array {
        return ['placeId' => $this->placeId];
    }

    public function apply(ResourceEntity $resource): ResourceEntity {
        Assertion::true($resource->hasWorkflow(), 'Resource does not have workflow.');
        Assertion::inArray(
            $this->placeId,
            EntityUtils::mapToIds($resource->getWorkflow()->getPlaces()),
            'Resource workflow does not contain desired place: ' . $this->placeId
        );
        $resource->setMarking([$this->placeId => true]);
        return $resource;
    }
}
