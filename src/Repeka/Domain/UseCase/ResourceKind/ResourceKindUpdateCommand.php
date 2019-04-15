<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceKindUpdateCommand extends ResourceClassAwareCommand implements AdjustableCommand {
    private $resourceKind;
    private $label;
    private $metadataList;
    private $workflow;
    private $allowedToClone;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @param ResourceKind | int $resourceKindOrId
     */
    public function __construct($resourceKindOrId, array $label, array $metadataList, bool $allowedToClone = false, $workflowOrId = null) {
        parent::__construct(ResourceKind::detectResourceClass($metadataList));
        $this->resourceKind = $resourceKindOrId;
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->workflow = $workflowOrId;
        $this->allowedToClone = $allowedToClone;
    }

    /** @return ResourceKind|int */
    public function getResourceKind() {
        return $this->resourceKind;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /** @return Metadata[] */
    public function getMetadataList(): array {
        return $this->metadataList;
    }

    public function isAllowedToClone(): bool {
        return $this->allowedToClone;
    }

    /** @return ResourceWorkflow|int|null */
    public function getWorkflow() {
        return $this->workflow;
    }
}
