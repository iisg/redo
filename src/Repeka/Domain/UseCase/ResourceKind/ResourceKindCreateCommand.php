<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceKindCreateCommand extends ResourceClassAwareCommand implements AdjustableCommand {
    protected $name;
    protected $label;
    protected $metadataList;
    protected $workflow;

    public function __construct(string $name, array $label, array $metadataList, $workflowOrId = null) {
        parent::__construct(ResourceKind::detectResourceClass($metadataList));
        $this->name = $name;
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->workflow = $workflowOrId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /** @return Metadata[] */
    public function getMetadataList(): array {
        return $this->metadataList;
    }

    /** @return ResourceWorkflow|int|null */
    public function getWorkflow() {
        return $this->workflow;
    }
}
