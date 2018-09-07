<?php
namespace Repeka\Domain\Metadata\MetadataImport;

use Repeka\Domain\Entity\ResourceContents;
use Respect\Validation\Validator;

class ImportResult {
    /** @var array[] */
    private $acceptedValues;
    /** @var string[][] */
    private $unfitTypeValues;
    /** @var string[] */
    private $invalidMetadataKeys;

    /**
     * @param ResourceContents $acceptedValues with metadata base ID keys
     * @param string[][] $unfitTypeValues with metadata base ID keys
     * @param string[] $invalidMetadataKeys
     */
    public function __construct(ResourceContents $acceptedValues, array $unfitTypeValues, array $invalidMetadataKeys) {
        Validator::arrayType()->each(
            Validator::arrayType(),
            Validator::intType()  // keys are base IDs
        )->assert($acceptedValues->toArray());
        Validator::arrayType()->each(Validator::stringType())->assert($invalidMetadataKeys);
        $this->acceptedValues = $acceptedValues;
        $this->unfitTypeValues = $unfitTypeValues;
        $this->invalidMetadataKeys = $invalidMetadataKeys;
    }

    public function getAcceptedValues(): ResourceContents {
        return $this->acceptedValues;
    }

    public function getUnfitTypeValues() {
        return $this->unfitTypeValues;
    }

    public function getInvalidMetadataKeys() {
        return $this->invalidMetadataKeys;
    }
}
