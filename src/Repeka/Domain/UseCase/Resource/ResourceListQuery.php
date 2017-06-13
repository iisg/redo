<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceListQuery extends NonValidatedCommand {
    private $includeResourcesWithSystemResourceKinds;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(bool $withSystemResourceKinds = false) {
        $this->includeResourcesWithSystemResourceKinds = $withSystemResourceKinds;
    }

    public function includeResourcesWithSystemResourceKinds(): bool {
        return $this->includeResourcesWithSystemResourceKinds;
    }
}
