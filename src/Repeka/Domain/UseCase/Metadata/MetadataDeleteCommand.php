<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataDeleteCommand extends AbstractCommand {
    /** @var Metadata */
    private $metadata;

    public function __construct(Metadata $metadata) {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
