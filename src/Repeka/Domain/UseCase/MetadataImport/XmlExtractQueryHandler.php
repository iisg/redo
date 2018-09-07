<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Metadata\MetadataImport\Executor\XmlImportExecutor;
use Repeka\Domain\Metadata\MetadataImport\Xml\XmlArrayDataExtractor;

class XmlExtractQueryHandler {
    /** @var XmlArrayDataExtractor */
    private $xmlArrayDataExtractor;

    public function __construct(XmlArrayDataExtractor $xmlArrayDataExtractor) {
        $this->xmlArrayDataExtractor = $xmlArrayDataExtractor;
    }

    public function handle(XmlExtractQuery $query): array {
        return $this->xmlArrayDataExtractor->import($query->getMappings(), $query->getXml());
    }
}
