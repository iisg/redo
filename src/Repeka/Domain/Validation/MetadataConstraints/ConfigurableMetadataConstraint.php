<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\DomainException;
use Stringy\StaticStringy;

interface ConfigurableMetadataConstraint {
    /**
     * Validates constraint configuration in metadata definition when metadata is created or updated.
     * @throws DomainException if more info is needed (its message will be visible in the response)
     */
    public function isConfigValid($config): bool;
}
