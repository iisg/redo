<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;

class ResourceKindUpdateCommand extends ResourceKindCreateCommand {
    private $resourceKind;

    public function __construct($resourceKindOrId, array $label, array $metadataList, array $displayStrategies, $workflowOrId = null) {
        parent::__construct($label, $metadataList, $displayStrategies, $workflowOrId);
        $this->resourceKind = $resourceKindOrId;
    }

    /** @return ResourceKind|int */
    public function getResourceKind() {
        return $this->resourceKind;
    }
}
