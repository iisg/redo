<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceKindCreateCommand extends ResourceClassAwareCommand implements AdjustableCommand {
    protected $label;
    protected $metadataList;
    protected $workflow;
    protected $displayStrategies;

    public function __construct(array $label, array $metadataList, array $displayStrategies = [], $workflowOrId = null) {
        parent::__construct($this->detectResourceClass($metadataList));
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->workflow = $workflowOrId;
        $this->displayStrategies = $displayStrategies;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /** @return Metadata[] */
    public function getMetadataList(): array {
        return $this->metadataList;
    }

    public function getDisplayStrategies(): array {
        return $this->displayStrategies;
    }

    /** @return ResourceWorkflow|int|null */
    public function getWorkflow() {
        return $this->workflow;
    }

    private function detectResourceClass(array $metadataList) {
        foreach ($metadataList as $metadata) {
            if ($metadata instanceof HasResourceClass) {
                $resourceClass = $metadata->getResourceClass();
                if ($resourceClass) {
                    return $resourceClass;
                }
            }
        }
    }
}
