<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataTopLevelPathQuery extends AbstractCommand implements NonValidatedCommand {
    /** @var Metadata */
    private $metadata;

    public function __construct(Metadata $metadata) {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
