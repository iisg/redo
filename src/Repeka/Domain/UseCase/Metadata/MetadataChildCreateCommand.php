<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataChildCreateCommand extends NonValidatedCommand {
    /** @var Metadata */
    private $base;
    /** @var Metadata */
    private $parent;

    public function __construct(Metadata $base, Metadata $parent) {
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
