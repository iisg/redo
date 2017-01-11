<?php
namespace Repeka\Domain\UseCase\ResourceKind;

class ResourceKindUpdateCommand extends ResourceKindCreateCommand {
    private $resourceKindId;

    public function __construct(int $resourceKindId, array $label, array $metadataList) {
        parent::__construct($label, $metadataList);
        $this->resourceKindId = $resourceKindId;
    }

    public function getResourceKindId(): int {
        return $this->resourceKindId;
    }
}
