<?php
namespace Repeka\Domain\Cqrs;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Utils\StringUtils;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCommand implements Command {

    /** @var User|null */
    protected $executor;

    public static function getCommandNameFromClassName($commandClass) {
        $successful = preg_match('#\\\\([a-z]+?)(Command|Query)?$#i', $commandClass, $matches);
        Assertion::true(!!$successful);
        return StringUtils::toSnakeCase($matches[1]);
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
