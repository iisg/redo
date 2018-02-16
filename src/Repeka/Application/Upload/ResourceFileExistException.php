<?php
namespace Repeka\Application\Upload;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;

class ResourceFileExistException extends DomainException {
    /** @var string */
    private $conflictingPath;

    public function __construct(ResourceEntity $resource, string $conflictingPath) {
        $this->conflictingPath = $conflictingPath;
        $conflictingPath = basename($conflictingPath);
        $resourceId = $resource->getId();
        $message = "File '$conflictingPath' already exists in path of resource #$resourceId'";
        parent::__construct($message);
    }

    public function getConflictingPath(): string {
        return $this->conflictingPath;
    }
}
