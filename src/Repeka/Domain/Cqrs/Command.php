<?php
namespace Repeka\Domain\Cqrs;

use Assert\Assertion;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Command {
    public function getCommandName() {
        $className = get_class($this);
        $successful = preg_match('#\\\\([a-z]+?)(Command|Query)?$#i', $className, $matches);
        Assertion::true(!!$successful);
        return $this->toSnakeCase($matches[1]);
    }

    /**
     * @see http://stackoverflow.com/a/19533226/878514
     */
    private function toSnakeCase($camelCase) {
        return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $camelCase));
    }

    public function __toString() {
        return $this->getCommandName();
    }
}
