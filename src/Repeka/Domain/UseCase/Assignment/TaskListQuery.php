<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\User;

class TaskListQuery extends NonValidatedCommand {
    /** @var User */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getUser(): User {
        return $this->user;
    }
}
