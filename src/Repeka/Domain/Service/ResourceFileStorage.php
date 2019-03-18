<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceFileStorage {
    public function getFileSystemPath(ResourceEntity $resource, string $resourcePath): string;

    public function getResourcePath(ResourceEntity $resource, string $fileSystemPath): string;

    /**
     * Returns array of arrays that represent configs of resource upload dirs.
     * @param ResourceEntity $resource
     * @return array of arrays with the following keys: id, label, path
     */
    public function uploadDirsForResource(ResourceEntity $resource): array;

    public function getFileContents(ResourceEntity $resource, string $path): string;

    public function getDirectoryContents(ResourceEntity $resource, string $path): array;
}
