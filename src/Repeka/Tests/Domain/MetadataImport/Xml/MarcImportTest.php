<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Repeka\Domain\MetadataImport\Xml\XmlArrayDataExtractor;

class MarcImportTest extends \PHPUnit_Framework_TestCase {
    private static $xmlString;

    /** @var XmlArrayDataExtractor */
    private $importer;

    /** @beforeClass */
    public static function loadSampleXmlMarcFile() {
        self::$xmlString = file_get_contents(__DIR__ . '/sample.marcxml');
    }

    /** @before */
    public function init() {
        $this->importer = new XmlArrayDataExtractor();
    }

    public function testImportTitle() {
        $result = $this->importer->import(['Tytuł' => '[tag="245"]>[code="a"]'], self::$xmlString);
        $this->assertEquals(['Tytuł' => ['Advances in water treatment and environmental management :']], $result);
    }

    public function testImportDescription() {
        $result = $this->importer->import(['Opis' => '[tag="245"]>[code="a"],[tag="245"]>[code="b"]'], self::$xmlString);
        $this->assertEquals(['Opis' => [
            'Advances in water treatment and environmental management :',
            'proceedings of the 1st International Conference (Lyon, France 27-29 june 1990) /',
        ]], $result);
    }

    public function testImportLangauge() {
        $result = $this->importer->import(['Język' => '[tag="008"]'], self::$xmlString);
        $this->assertEquals(['Język' => ['051129s1991    xxka   | |||||1|| | eng||']], $result);
    }

    public function testImportMultipleMetadataAtOnce() {
        $result = $this->importer->import([
            'Rok wydania' => '[tag="111"]>[code="d"]',
            'Data pojawienia się pomysłu o napisaniu tej książki' => '[tag="700"]>[code="d"]',
            'Jeszcze jakaś inna wartość' => '[tag="700"]>[code="9"]',
        ], self::$xmlString);
        $this->assertEquals([
            'Rok wydania' => ['1990 ;'],
            'Data pojawienia się pomysłu o napisaniu tej książki' => ['(1942- ).'],
            'Jeszcze jakaś inna wartość' => ['35884', '72169'],
        ], $result);
    }
}
