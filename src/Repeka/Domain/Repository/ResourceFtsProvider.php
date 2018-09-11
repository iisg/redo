<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;

interface ResourceFtsProvider {
    /** @return ResourceEntity[] */
    public function search(ResourceListFtsQuery $phrase);
}
