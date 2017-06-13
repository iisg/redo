<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceKindListQuery extends NonValidatedCommand {
    private $includeSystemResourceKinds;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(bool $includeSystemResourceKinds = true) {
        $this->includeSystemResourceKinds = $includeSystemResourceKinds;
    }

    public function includeSystemResourceKinds(): bool {
        return $this->includeSystemResourceKinds;
    }
}
