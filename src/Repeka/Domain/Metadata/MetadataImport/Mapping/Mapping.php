<?php
namespace Repeka\Domain\Metadata\MetadataImport\Mapping;

use Repeka\Domain\Entity\Metadata;

class Mapping {
    /** @var Metadata */
    private $metadata;
    /** @var string | null */
    private $importKey;
    /** @var array */
    private $transformsConfig;
    /** @var Mapping[] */
    private $submetadataMappings;

    public function __construct(Metadata $metadata, ?string $importKey, array $transformsConfig, array $submetadataMappings) {
        $this->metadata = $metadata;
        $this->importKey = $importKey;
        $this->transformsConfig = $transformsConfig;
        $this->submetadataMappings = $submetadataMappings;
    }

    public function getMetadata(): Metadata {
        return $this->metadata;
    }

    public function getImportKey(): ?string {
        return $this->importKey;
    }

    public function getTransformsConfig(): array {
        return $this->transformsConfig;
    }

    /**
     * @return Mapping[]
     */
    public function getSubmetadataMappings(): array {
        return $this->submetadataMappings;
    }
}
