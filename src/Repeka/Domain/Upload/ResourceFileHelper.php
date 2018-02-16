<?php
namespace Repeka\Domain\Upload;

use Repeka\Application\Upload\ResourceFileExistException;
use Repeka\Domain\Entity\ResourceEntity;

interface ResourceFileHelper {
    /** @throws ResourceFileExistException */
    public function moveFilesToDestinationPaths(ResourceEntity $resource): int;

    public function toAbsolutePath(string $path): string;
}
