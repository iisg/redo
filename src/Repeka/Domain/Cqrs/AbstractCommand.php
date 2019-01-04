<?php
namespace Repeka\Domain\Cqrs;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\User;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCommand implements Command {

    /** @var User|null */
    protected $executor;

    public static function getCommandNameFromClassName($commandClass) {
        $successful = preg_match('#\\\\([a-z]+?)(Command|Query)?$#i', $commandClass, $matches);
        Assertion::true(!!$successful);
        return self::toSnakeCase($matches[1]);
    }

    /**
     * @see http://stackoverflow.com/a/19533226/878514
     */
    private static function toSnakeCase($camelCase) {
        return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $camelCase));
    }

    public function getCommandClassName() {
        return get_class($this);
    }

    public function getCommandName() {
        return self::getCommandNameFromClassName($this->getCommandClassName());
    }

    public function __toString() {
        return $this->getCommandName();
    }

    public function getExecutor(): ?User {
        return $this->executor;
    }

    public function getRequiredRole(): ?SystemRole {
        return SystemRole::ADMIN();
    }
}
