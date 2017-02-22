<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class MetadataListQuery extends NonValidatedCommand {
    private $parentId;

    public function __construct(int $parentId = null) {
        $this->parentId = $parentId;
    }

    public function getId() {
        return $this->parentId;
    }
}
