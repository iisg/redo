<?php
namespace Repeka\Domain\Metadata\MetadataImport\Config;

use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;

class ImportConfig {
    /** @var Mapping[] */
    private $mappings;
    /** @var string[] */
    private $invalidMetadataKeys;

    /**
     * @param Mapping[] $mappings without keys
     * @param string[] $invalidMetadataKeys
     */
    public function __construct(array $mappings, array $invalidMetadataKeys) {
        $this->mappings = $mappings;
        $this->invalidMetadataKeys = $invalidMetadataKeys;
    }

    /** @return Mapping[] */
    public function getMappings(): array {
        return $this->mappings;
    }

    /** @return string[] */
    public function getInvalidMetadataKeys(): array {
        return $this->invalidMetadataKeys;
    }
}
