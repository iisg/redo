<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceKindCreateCommand extends Command {
    private $label;
    private $metadataList;
    private $workflow;
    private $resourceClass;

    public function __construct(array $label, array $metadataList, string $resourceClass, ResourceWorkflow $workflow = null) {
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->resourceClass = $resourceClass;
        $this->workflow = $workflow;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getMetadataList(): array {
        return $this->metadataList;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function getWorkflow(): ?ResourceWorkflow {
        return $this->workflow;
    }
}
