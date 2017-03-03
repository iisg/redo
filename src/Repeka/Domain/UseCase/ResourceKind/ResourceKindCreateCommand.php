<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceKindCreateCommand extends Command {
    protected $label;

    protected $metadataList;

    private $workflow;

    public function __construct(array $label, array $metadataList, ResourceWorkflow $workflow = null) {
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->workflow = $workflow;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getMetadataList(): array {
        return $this->metadataList;
    }

    public function getWorkflow(): ?ResourceWorkflow {
        return $this->workflow;
    }
}
