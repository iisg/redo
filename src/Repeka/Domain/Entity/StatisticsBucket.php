<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Utils\ImmutableIteratorAggregate;

class StatisticsBucket extends ImmutableIteratorAggregate {
    public function __construct(array $row, string $aggregation) {
        parent::__construct($row);
        $this->contents['bucketLabel'] = $this->createBucketLabel($aggregation);
    }

    private function createBucketLabel(string $aggregation): string {
        switch ($aggregation) {
            case 'day':
                return $this->getBucket()->format('d.m.Y');
            case 'week':
                return $this->getBucket()->format('d.m.Y') . ' - ' . date('d.m.Y', $this->getBucket()->getTimestamp() + 7 * 86400);
            case 'month':
                return $this->getBucket()->format('m.Y');
            case 'year':
                return $this->getBucket()->format('Y');
            default:
                return 'Total';
        }
    }

    public function getEventName(): string {
        return $this->contents['eventName'];
    }

    public function getEventGroup(): string {
        return $this->contents['eventGroup'];
    }

    public function getCount(): int {
        return $this->contents['count'];
    }

    public function getBucket(): \DateTimeImmutable {
        return $this->contents['bucket'];
    }

    public function getBucketLabel(): string {
        return $this->contents['bucketLabel'];
    }

    public function getResourceId(): ?int {
        return $this->contents['resourceId'] ?? null;
    }

    public function getResourceLabel(): ?string {
        return $this->contents['resourceLabel'] ?? null;
    }
}
