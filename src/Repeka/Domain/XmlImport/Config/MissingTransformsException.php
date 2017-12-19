<?php
namespace Repeka\Domain\XmlImport\Config;

use Repeka\Domain\XmlImport\XmlImportException;

class MissingTransformsException extends XmlImportException {
    /** @var string[] */
    private $missingTransformNames;

    /**
     * @param string[] $missingTransformNames
     */
    public function __construct(array $missingTransformNames) {
        $namesString = implode(', ', $missingTransformNames);
        parent::__construct('missingTransforms', ['names' => $namesString]);
        $this->missingTransformNames = $missingTransformNames;
    }

    public function getMissingTransformNames(): array {
        return $this->missingTransformNames;
    }
}
