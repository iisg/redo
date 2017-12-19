<?php
namespace Repeka\Domain\XmlImport\Config;

use Repeka\Domain\XmlImport\XmlImportException;

class InvalidTopLevelKeysException extends XmlImportException {
    public function __construct() {
        parent::__construct("invalidTopKeys");
    }
}
