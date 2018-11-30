<?php
namespace Repeka\Application\Command\PkImport\XmlExtractStrategy;

use Repeka\Domain\Metadata\MetadataImport\Xml\XmlArrayDataExtractor;

class PkKohaDumpXmlExtractor implements PkImportXmlExtractor {

    /** @var XmlArrayDataExtractor */
    private $xmlArrayDataExtractor;

    public function __construct() {
        $this->xmlArrayDataExtractor = new XmlArrayDataExtractor();
    }

    public function extractAllResources(\SimpleXmlElement $xml) {
        return $xml->xpath('/*/*');
    }

    public function extractResourceData(\SimpleXmlElement $resource): array {
        return $this->xmlArrayDataExtractor->import(
            [
                'ID' => '[tag=001]',
                'ukd_symbol_klasyfikacji' => '[tag=153]>[code=a]',
                'ukd_dopowiedzenie_slowne' => '[tag=153]>[code=j],[tag=153]>[code=k]',
            ],
            $resource->asXML()
        );
    }
}
