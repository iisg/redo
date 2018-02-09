<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class MetadataListByParentIdQuery extends AbstractCommand implements NonValidatedCommand {
    private $parentId;

    public function __construct(int $parentId) {
        $this->parentId = $parentId;
    }

    public function getParentId(): int {
        return $this->parentId;
    }
}
