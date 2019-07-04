<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Utils\ArrayUtils;

class BulkChangeFactory {
    use CommandBusAware;

    /** @var BulkChange[] */
    private $bulkChanges;

    public function __construct(iterable $bulkChanges) {
        $this->bulkChanges = $bulkChanges;
    }

    /** @return BulkChange[] */
    private function getBulkChanges(): array {
        if (!is_array($this->bulkChanges)) {
            $this->bulkChanges = ArrayUtils::keyBy(
                $this->bulkChanges,
                function (BulkChange $bulkChange) {
                    return $bulkChange->getActionName();
                }
            );
        }
        return $this->bulkChanges;
    }

    public function create(array $params): BulkChange {
        $action = $params['action'];
        $change = $params['change'];
        $bulkChanges = $this->getBulkChanges();
        Assertion::keyExists($bulkChanges, $action, 'No implementation for bulk change: ' . $action);
        return $bulkChanges[$action]->createForChange($change);
    }
}
