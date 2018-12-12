<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceFileStorage {
    public function getFileSystemPath(ResourceEntity $resource, string $path): string;

    public function uploadDirsForResource(ResourceEntity $resource): array;
}
