<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceKindListQuery extends NonValidatedCommand {
    private $includeSystemResources;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(bool $includeSystemResources = true) {
        $this->includeSystemResources = $includeSystemResources;
    }

    public function includeSystemResources(): bool {
        return $this->includeSystemResources;
    }
}
