<?php
namespace Repeka\Domain\Metadata\MetadataImport;

class MetadataImportContext {
    private $parentMetadataValue;
    private $valuesBasedOnImportKey;

    public function __construct($parentMetadataValue, $valuesBasedOnImportKey) {
        $this->parentMetadataValue = $parentMetadataValue;
        $this->valuesBasedOnImportKey = $valuesBasedOnImportKey;
    }

    public function getParentMetadataValue() {
        return $this->parentMetadataValue;
    }

    public function getValuesBasedOnImportKey() {
        return $this->valuesBasedOnImportKey;
    }
}
