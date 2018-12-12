<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use PHPUnit_Framework_TestCase;
use Repeka\Domain\Metadata\MetadataImport\Xml\MarcxmlArrayDataExtractor;

class MarcImportTest extends PHPUnit_Framework_TestCase {

    private static $xmlString;

    /** @var MarcxmlArrayDataExtractor */
    private $importer;

    /** @beforeClass */
    public static function loadSampleXmlMarcFile() {
        self::$xmlString = file_get_contents(__DIR__ . '/bib-103684.marcxml');
    }

    /** @before */
    public function init() {
        $this->importer = new MarcxmlArrayDataExtractor();
    }

    public function testImport() {
        $marcXmlResource = $this->importer->import(self::$xmlString);
        $this->assertCount(1, $marcXmlResource['260']);
        $this->assertCount(2, $marcXmlResource['260'][0]['b']);
        $this->assertEquals('nakł. S. Orgelbranda Synów :', $marcXmlResource['260'][0]['b'][0]);
        $this->assertContains('Drzeworytnia Warszawska,', $marcXmlResource['260'][0]['b'][1]);
        $this->assertCount(7, $marcXmlResource['246']);
        $this->assertCount(5, $marcXmlResource['246'][4]); //order ind1 ind2 a
        $this->assertEquals('Willanów :', $marcXmlResource['246'][4]['a'][0]);
        $this->assertContains(
            'album widoków i pamiątek oraz kopje z obrazów Galerii Willanowskiej wykonane na drzewie w Drzeworytni Warszawskiej',
            $marcXmlResource['246'][4]['b'][0]
        );
        $this->assertEquals("1", $marcXmlResource['856'][1]['ind2']);
        $this->assertEquals("4", $marcXmlResource['856'][1]['ind1']);
        $this->assertEquals(
            ['0', '1', '2', '4', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'i', 'o', 'p', 'r', 'w', 'y'],
            $marcXmlResource['952'][0]['order']
        );
    }
}
