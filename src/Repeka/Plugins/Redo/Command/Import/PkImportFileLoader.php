<?php
namespace Repeka\Plugins\Redo\Command\Import;

final class PkImportFileLoader {
    private function __construct() {
    }

    public static function load(string $inputFile): \SimpleXmlElement {
        if (!file_exists($inputFile) || !is_readable($inputFile)) {
            throw new \RuntimeException('Input file is not readable: ' . $inputFile);
        }
        return simplexml_load_string(self::utf8ForXml(file_get_contents($inputFile)));
    }

    /**
     * @see https://stackoverflow.com/a/14464026/878514
     */
    private static function utf8ForXml($string) {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
    }
}
