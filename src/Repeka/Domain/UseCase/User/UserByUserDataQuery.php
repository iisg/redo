<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceEntity;

class UserByUserDataQuery extends NonValidatedCommand {
    /** @var ResourceEntity */
    private $userData;

    public function __construct(ResourceEntity $userData) {
        $this->userData = $userData;
    }

    public function getUserData(): ResourceEntity {
        return $this->userData;
    }
}
