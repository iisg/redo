<?php
namespace Repeka\Domain\Upload;

use Repeka\Application\Upload\AttachmentsExistException;
use Repeka\Domain\Entity\ResourceEntity;

interface ResourceAttachmentHelper {
    /** @throws AttachmentsExistException */
    public function moveFilesToDestinationPaths(ResourceEntity $resource): int;
}
