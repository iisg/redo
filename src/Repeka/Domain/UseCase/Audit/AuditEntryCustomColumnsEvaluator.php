<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class AuditEntryCustomColumnsEvaluator {
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    /**
     * @param AuditEntry $entry
     * @param string[] $customColumns
     * @return string[]
     */
    public function evaluateCustomColumns(AuditEntry $entry, array $customColumns): array {
        $data = $entry->getData();
        foreach (['before', 'after'] as $dataField) {
            if (isset($data[$dataField]) && isset($data[$dataField]['resource'])) {
                $evaluated = [];
                $contents = ResourceContents::fromArray($data[$dataField]['resource']['contents']);
                foreach ($customColumns as $displayStrategy) {
                    if (!trim($displayStrategy)) {
                        $evaluated[$displayStrategy] = "";
                    } else {
                        $evaluated[$displayStrategy] = $this->displayStrategyEvaluator->render($contents, $displayStrategy);
                    }
                }
                return $evaluated;
            }
        }
        return [];
    }
}
