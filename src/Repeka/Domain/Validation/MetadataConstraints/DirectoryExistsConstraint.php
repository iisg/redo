<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceFileStorage;
use Respect\Validation\Validator;

class DirectoryExistsConstraint extends AbstractMetadataConstraint {
    /** @var ResourceFileStorage */
    private $resourceFileHelper;

    public function __construct(ResourceFileStorage $resourceFileHelper) {
        $this->resourceFileHelper = $resourceFileHelper;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::DIRECTORY];
    }

    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null) {
        $path = $this->resourceFileHelper->getFileSystemPath($resource, $metadataValue);
        Validator::exists()->readable()->directory()->assert($path);
    }
}
