<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\Metadata;

class ResourceWorkflowUsingMetadataAsAssigneeQuery extends NonValidatedCommand {
    /** @var Metadata */
    private $metadata;

    public function __construct(Metadata $metadata) {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
