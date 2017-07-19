<?php
namespace Repeka\Domain\Upload;

use Repeka\Application\Upload\ResourceFilesExistException;
use Repeka\Domain\Entity\ResourceEntity;

interface ResourceFileHelper {
    /** @throws ResourceFilesExistException */
    public function moveFilesToDestinationPaths(ResourceEntity $resource): int;

    public function toAbsolutePath(string $path): string;
}
