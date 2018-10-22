<?php
namespace Repeka\Application\Command\PkImport\XmlExtractStrategy;

class PkResourcesDumpXmlExtractor implements PkImportXmlExtractor {
    public function extractAllResources(\SimpleXmlElement $xml) {
        return $xml->xpath('/*/*');
    }

    public function extractResourceData(\SimpleXmlElement $resource): array {
        $resourceData = current($resource->attributes());
        $metadataList = $resource->metadata;
        $terms = [];
        foreach ($metadataList as $metadata) {
            $termId = (string)$metadata['TERM_ID'];
            $terms[] = $termId;
            $metadataData = [];
            foreach (current($metadata->attributes()) as $attr => $value) {
                $metadataData[$attr] = $value;
            }
            $resourceData[$termId][] = $metadataData;
        }
        return $resourceData;
    }
}
