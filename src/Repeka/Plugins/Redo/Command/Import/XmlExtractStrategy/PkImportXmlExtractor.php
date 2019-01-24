<?php
namespace Repeka\Plugins\Redo\Command\Import\XmlExtractStrategy;

interface PkImportXmlExtractor {
    public function extractAllResources(\SimpleXmlElement $xml);

    public function extractResourceData(\SimpleXmlElement $resource): array;
}
