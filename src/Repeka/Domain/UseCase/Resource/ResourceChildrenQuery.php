<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceChildrenQuery extends NonValidatedCommand {
    /** @var int */
    private $parentId;

    public function __construct(int $parentId) {
        $this->parentId = $parentId;
    }

    public function getParentId(): int {
        return $this->parentId;
    }
}
