<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceFileStorage;
use Respect\Validation\Validator;

class FileExistsConstraint extends AbstractMetadataConstraint {
    /** @var ResourceFileStorage */
    private $fileStorage;

    public function __construct(ResourceFileStorage $fileStorage) {
        $this->fileStorage = $fileStorage;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::FILE];
    }

    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource): void {
        $path = $this->fileStorage->getFileSystemPath($resource, $metadataValue);
        Validator::exists()->readable()->file()->assert($path);
    }
}
