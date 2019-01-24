<?php
namespace Repeka\Plugins\Redo\Command\Import;

final class PkImportFileLoader {
    private function __construct() {
    }

    public static function load(string $inputFile): \SimpleXmlElement {
        if (!file_exists($inputFile) || !is_readable($inputFile)) {
            throw new \RuntimeException('Input file is not readable: ' . $inputFile);
        }
        return simplexml_load_file($inputFile);
    }
}
