<?php
namespace Repeka\Domain\MetadataImport;

use Repeka\Domain\Factory\ResourceContentsNormalizer;

class ImportResultBuilder {
    /** @var array[] */
    private $acceptedValues = [];
    /** @var string[][] */
    private $unfitTypeValues = [];
    /** @var string[] */
    private $invalidMetadataKeys;

    /**
     * @param string[] $invalidMetadataKeys
     */
    public function __construct(array $invalidMetadataKeys) {
        $this->invalidMetadataKeys = $invalidMetadataKeys;
    }

    /**
     * @param array $values
     */
    public function addAcceptedValues(int $metadataId, array $values): void {
        $this->acceptedValues[$metadataId] = $values;
    }

    /**
     * @param string[] $values
     */
    public function addUnfitTypeValues(int $metadataId, array $values): void {
        $this->unfitTypeValues[$metadataId] = $values;
    }

    public function build(ResourceContentsNormalizer $normalizer): ImportResult {
        $notEmptyCallback = function (array $array) {
            return !empty($array);
        };
        $this->acceptedValues = $normalizer->normalize(array_filter($this->acceptedValues, $notEmptyCallback));
        $this->unfitTypeValues = array_filter($this->unfitTypeValues, $notEmptyCallback);
        return new ImportResult($this->acceptedValues, $this->unfitTypeValues, $this->invalidMetadataKeys);
    }
}
