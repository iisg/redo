<?php
namespace Repeka\CoreModule\Domain\Cqrs;

use Assert\Assertion;

abstract class Command {
    public function getCommandName() {
        $className = get_class($this);
        $successful = preg_match('#^Repeka\\\\([A-Za-z]+)Module\\\\.*?\\\\?([A-Za-z]+?)(Command|Query)?$#', $className, $matches);
        Assertion::true(!!$successful);
        $underscoredCommandName = $this->toSnakeCase($matches[2]);
        return strtolower("$matches[1].$underscoredCommandName");
    }

    /**
     * @see http://stackoverflow.com/a/19533226/878514
     */
    private function toSnakeCase($camelCase) {
        return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $camelCase));
    }
}
