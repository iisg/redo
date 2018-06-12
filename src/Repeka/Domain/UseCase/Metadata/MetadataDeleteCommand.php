<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataDeleteCommand extends ResourceClassAwareCommand {
    /** @var Metadata */
    private $metadata;

    public function __construct(Metadata $metadata) {
        parent::__construct($metadata);
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
