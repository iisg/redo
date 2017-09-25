<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

interface AssignmentFinder {
    /** @return ResourceEntity[] */
    public function findAssignedResources(User $user): array;
}
