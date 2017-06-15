<?php
namespace Repeka\Application\Upload;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;

class AttachmentsExistException extends DomainException {
    private $existingFiles;

    public function __construct(ResourceEntity $resource, array $existingFiles) {
        $this->existingFiles = $existingFiles;
        $fileName = basename(reset($existingFiles));
        $resourceId = $resource->getId();
        $message = "File '$fileName' already exists in path of resource #$resourceId'";
        parent::__construct($message);
    }

    public function getExistingFiles(): array {
        return $this->existingFiles;
    }
}
