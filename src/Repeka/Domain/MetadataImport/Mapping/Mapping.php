<?php
namespace Repeka\Domain\MetadataImport\Mapping;

use Repeka\Domain\Entity\Metadata;

class Mapping {
    /** @var Metadata */
    private $metadata;
    /** @var string */
    private $importKey;
    /** @var array */
    private $transformsConfig;

    public function __construct(Metadata $metadata, string $importKey, array $transformsConfig) {
        $this->metadata = $metadata;
        $this->importKey = $importKey;
        $this->transformsConfig = $transformsConfig;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }

    public function getImportKey(): string {
        return $this->importKey;
    }

    public function getTransformsConfig(): array {
        return $this->transformsConfig;
    }
}
