<?php
namespace Repeka\Application\Command\PkImport\XmlExtractStrategy;

interface PkImportXmlExtractor {
    public function extractAllResources(\SimpleXmlElement $xml);

    public function extractResourceData(\SimpleXmlElement $resource): array;
}
