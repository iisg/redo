<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class MetadataChildWithBaseCreateCommand extends NonValidatedCommand {
    /** @var Metadata */
    private $base;
    /** @var Metadata */
    private $parent;

    public function __construct(Metadata $parent, Metadata $base) {
        $this->base = $base;
        $this->parent = $parent;
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
}
