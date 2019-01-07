<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\User;

interface Audit {
    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function newEntry(string $type, ?User $user, array $data = [], bool $successful = true): void;
}
