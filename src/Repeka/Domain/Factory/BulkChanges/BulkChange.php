<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\StringUtils;

abstract class BulkChange {
    public function getActionName(): string {
        return self::getActionNameFromClassName(get_class($this));
    }

    private static function getActionNameFromClassName($bulkChangeClass) {
        $successful = preg_match('#\\\\([a-z]+?)(BulkChange)?$#i', $bulkChangeClass, $matches);
        Assertion::true(!!$successful);
        return StringUtils::toSnakeCase($matches[1]);
    }

    public function toArray(): array {
        return [
            'action' => $this->getActionName(),
            'change' => $this->getChangeConfig(),
        ];
    }

    abstract public function createForChange(array $changeConfig): self;

    abstract public function apply(ResourceEntity $resource): ResourceEntity;

    public function applyForPreview(ResourceEntity $resource): ResourceEntity {
        return $this->apply($resource);
    }

    abstract protected function getChangeConfig(): array;
}
