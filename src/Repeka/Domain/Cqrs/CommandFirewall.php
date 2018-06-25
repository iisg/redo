<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;

interface CommandFirewall {
    /**
     * @param Command $command
     * @param User $executor
     * @throws InsufficientPrivilegesException when the command execution should be prevented
     */
    public function ensureCanExecute(Command $command, User $executor): void;
}
