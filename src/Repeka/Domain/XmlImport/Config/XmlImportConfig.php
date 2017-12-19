<?php
namespace Repeka\Domain\XmlImport\Config;

use Repeka\Domain\XmlImport\Mapping\Mapping;
use Repeka\Domain\XmlImport\Transform\Transform;

class XmlImportConfig {
    /** @var Transform[] */
    private $transforms;
    /** @var Mapping[] */
    private $mappings;
    /** @var string[] */
    private $invalidMetadataKeys;

    /**
     * @param Transform[] $transforms with name string keys
     * @param Mapping[] $mappings without keys
     * @param string[] $invalidMetadataKeys
     */
    public function __construct(array $transforms, array $mappings, array $invalidMetadataKeys) {
        $this->transforms = $transforms;
        $this->mappings = $mappings;
        $this->invalidMetadataKeys = $invalidMetadataKeys;
    }

    /** @return Transform[] with name string keys */
    public function getTransforms(): array {
        return $this->transforms;
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
