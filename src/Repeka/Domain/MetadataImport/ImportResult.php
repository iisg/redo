<?php
namespace Repeka\Domain\MetadataImport;

use Respect\Validation\Validator;

class ImportResult {
    /** @var array[] */
    private $acceptedValues;
    /** @var string[][] */
    private $unfitTypeValues;
    /** @var string[] */
    private $invalidMetadataKeys;

    /**
     * @param array[] $acceptedValues with metadata base ID keys
     * @param string[][] $unfitTypeValues with metadata base ID keys
     * @param string[] $invalidMetadataKeys
     */
    public function __construct(array $acceptedValues, array $unfitTypeValues, array $invalidMetadataKeys) {
        Validator::arrayType()->each(
            Validator::arrayType(),
            Validator::intType()  // keys are base IDs
        )->assert($acceptedValues);
        Validator::arrayType()->each(Validator::stringType())->assert($invalidMetadataKeys);
        $this->acceptedValues = $acceptedValues;
        $this->unfitTypeValues = $unfitTypeValues;
        $this->invalidMetadataKeys = $invalidMetadataKeys;
    }

    public function getAcceptedValues() {
        return $this->acceptedValues;
    }

    public function getUnfitTypeValues() {
        return $this->unfitTypeValues;
    }

    public function getInvalidMetadataKeys() {
        return $this->invalidMetadataKeys;
    }
}
