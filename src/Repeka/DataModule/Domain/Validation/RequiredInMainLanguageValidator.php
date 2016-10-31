<?php
namespace Repeka\DataModule\Domain\Validation;

class RequiredInMainLanguageValidator {
    private $mainLanguage;

    public function __construct($mainLanguage) {
        $this->mainLanguage = $mainLanguage;
    }

    public function isValid($value) {
        return isset($value[$this->mainLanguage]) && trim($value[$this->mainLanguage]);
    }
}
