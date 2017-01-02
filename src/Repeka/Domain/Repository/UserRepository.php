<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\User;

interface UserRepository {
    public function findOne($getId): User;

    public function save(User $user): User;
}
