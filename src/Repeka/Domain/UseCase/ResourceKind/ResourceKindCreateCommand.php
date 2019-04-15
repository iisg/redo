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
    protected $allowedToClone;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     **/
    public function __construct(string $name, array $label, array $metadataList, bool $allowedToClone = false, $workflowOrId = null) {
        parent::__construct(ResourceKind::detectResourceClass($metadataList));
        $this->name = $name;
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->workflow = $workflowOrId;
        $this->allowedToClone = $allowedToClone;
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

    public function isAllowedToClone(): bool {
        return $this->allowedToClone;
    }

    /** @return ResourceWorkflow|int|null */
    public function getWorkflow() {
        return $this->workflow;
    }
}
