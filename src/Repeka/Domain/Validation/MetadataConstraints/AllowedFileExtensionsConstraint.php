<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Respect\Validation\Validator;

class AllowedFileExtensionsConstraint extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::FILE];
    }

    public function getConstraintName(): string {
        return 'allowedFileExtensions';
    }

    public function isConfigValid($allowedExtensions): bool {
        return !$allowedExtensions || (Validator::arrayType()->validate($allowedExtensions)
                && Validator::notEmpty()->validate($allowedExtensions));
    }

    /** @inheritdoc */
    public function validateSingle(Metadata $metadata, $filename, ResourceEntity $resource): void {
        $allowedExtensions = $metadata->getConstraints()[$this->getConstraintName()] ?? null;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($allowedExtensions && !in_array(strtolower($extension), $allowedExtensions)) {
            throw new \DomainException('File has forbidden extension');
        };
    }
}
