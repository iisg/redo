<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataChildWithBaseCreateCommand extends ResourceClassAwareCommand implements NonValidatedCommand {
    /** @var Metadata */
    private $base;
    /** @var Metadata */
    private $parent;
    private $newChildMetadata;

    public function __construct(Metadata $parent, Metadata $base, array $newChildMetadata) {
        parent::__construct($parent);
        $this->base = $base;
        $this->parent = $parent;
        $this->newChildMetadata = $newChildMetadata;
    }

    /**
     * @return Metadata
     */
    public function getBase(): Metadata {
        return $this->base;
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
