<?php
namespace Repeka\Domain\Entity;

interface User {
    public function getId(): int;

    /**
     * @return string[]
     */
    public function getStaticPermissions(): array;

    /**
     * @param string[] $permissions
     */
    public function updateStaticPermissions(array $permissions);
}
