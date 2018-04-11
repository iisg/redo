<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\User;

class UserGroupsQuery extends AbstractCommand implements NonValidatedCommand {
    /** @var User */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getUser(): User {
        return $this->user;
    }
}
