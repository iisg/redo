<?php
namespace Repeka\Domain\XmlImport\Transform;

use Repeka\Domain\XmlImport\XmlImportException;

class InvalidTransformException extends XmlImportException {
    /** @var string */
    private $transformName;
    /** @var array */
    private $paramNames;

    public function __construct(string $transformName, array $paramNames) {
        $paramNamesString = implode(', ', $paramNames);
        parent::__construct('invalidTransform', ['name' => $transformName, 'params' => $paramNamesString]);
        $this->transformName = $transformName;
        $this->paramNames = $paramNames;
    }

    public function getTransformName(): string {
        return $this->transformName;
    }

    public function getParamNames(): array {
        return $this->paramNames;
    }
}
