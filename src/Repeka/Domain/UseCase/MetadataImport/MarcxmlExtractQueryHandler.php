<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Metadata\MetadataImport\Xml\MarcxmlArrayDataExtractor;

class MarcxmlExtractQueryHandler {
    /** @var MarcxmlArrayDataExtractor */
    private $xmlArrayDataExtractor;

    public function __construct(MarcxmlArrayDataExtractor $xmlArrayDataExtractor) {
        $this->xmlArrayDataExtractor = $xmlArrayDataExtractor;
    }

    public function handle(MarcxmlExtractQuery $query): array {
        return $this->xmlArrayDataExtractor->import($query->getXml());
    }
}
