<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;

class ResourceListQuery extends Command {
    /** @var ResourceKind[] */
    private $resourceKinds;
    /** @var string[] */
    private $resourceClasses;
    /**
     * @var bool
     */
    private $onlyTopLevel;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    private function __construct(array $resourceClasses, array $resourceKinds, bool $onlyTopLevel) {
        $this->resourceKinds = $resourceKinds;
        $this->resourceClasses = $resourceClasses;
        $this->onlyTopLevel = $onlyTopLevel;
    }

    public static function builder(): ResourceListQueryBuilder {
        return new ResourceListQueryBuilder();
    }

    public static function withParams(array $resourceClasses, array $resourceKinds, bool $onlyTopLevel): ResourceListQuery {
        return new self($resourceClasses, $resourceKinds, $onlyTopLevel);
    }

    /** @return string[] */
    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    /** @return ResourceKind[] */
    public function getResourceKinds(): array {
        return $this->resourceKinds;
    }

    public function onlyTopLevel(): bool {
        return $this->onlyTopLevel;
    }
}
