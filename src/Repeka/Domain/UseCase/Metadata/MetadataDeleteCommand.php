<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;

class MetadataDeleteCommand extends Command {
    /** @var Metadata */
    private $metadata;

    public function __construct(Metadata $metadata) {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
