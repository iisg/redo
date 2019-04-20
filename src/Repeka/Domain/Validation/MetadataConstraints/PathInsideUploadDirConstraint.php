<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;

class PathInsideUploadDirConstraint extends AbstractMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::DIRECTORY, MetadataControl::FILE];
    }

    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null): void {
        if (preg_match('#[/]\.\.[/$]#', $metadataValue)) {
            throw new DomainException(
                "You can't refer to the parent directory like in $metadataValue. If you wanted to hack us, you need to try better!",
                Response::HTTP_I_AM_A_TEAPOT
            );
        }
    }
}
