<?php
namespace Repeka\Domain\Repository;

interface RepositoryProvider {
    public function getForEntityType(string $entityType);
}
