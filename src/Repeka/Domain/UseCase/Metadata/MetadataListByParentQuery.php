<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataListByParentQuery extends AbstractCommand implements NonValidatedCommand {
    private $parent;

    public function __construct(Metadata $parent) {
        $this->parent = $parent;
    }

    public function getParent(): Metadata {
        return $this->parent;
    }
}
