<?php
namespace Repeka\Domain\XmlImport\Executor;

use Repeka\Domain\XmlImport\XmlImportException;

class MissingSubfieldsException extends XmlImportException {
    /** @var string */
    private $mappingName;
    /** @var string[] */
    private $missingSubfieldNames;

    /**
     * @param string[] $missingSubfieldNames
     */
    public function __construct(string $mappingName, array $missingSubfieldNames) {
        $namesString = implode(', ', $missingSubfieldNames);
        parent::__construct('missingSubfields', ['mapping' => $mappingName, 'names' => $namesString]);
        $this->mappingName = $mappingName;
        $this->missingSubfieldNames = $missingSubfieldNames;
    }

    public function getMappingName(): string {
        return $this->mappingName;
    }

    public function getMissingSubfieldNames(): array {
        return $this->missingSubfieldNames;
    }
}
