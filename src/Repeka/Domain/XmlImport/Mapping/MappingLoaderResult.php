<?php
namespace Repeka\Domain\XmlImport\Mapping;

class MappingLoaderResult {
    /** @var Mapping[] */
    private $loadedMappings;
    /** @var string[] */
    private $keysMissingFromResourceKind;

    /**
     * @param Mapping[] $loadedMappings
     * @param string[] $keysMissingFromResourceKind
     */
    public function __construct(array $loadedMappings, array $keysMissingFromResourceKind) {
        $this->loadedMappings = $loadedMappings;
        $this->keysMissingFromResourceKind = $keysMissingFromResourceKind;
    }

    public function getLoadedMappings(): array {
        return $this->loadedMappings;
    }

    public function getKeysMissingFromResourceKind(): array {
        return $this->keysMissingFromResourceKind;
    }
}
