<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataChildCreateCommand extends AbstractCommand {
    /** @var Metadata */
    private $parent;
    private $newChildMetadata;

    public function __construct(Metadata $parent, array $newChildMetadata) {
        $this->parent = $parent;
        $this->newChildMetadata = $newChildMetadata;
    }

    /**
     * @return Metadata
     */
    public function getParent(): Metadata {
        return $this->parent;
    }

    public function getNewChildMetadata(): array {
        return $this->newChildMetadata;
    }
}
