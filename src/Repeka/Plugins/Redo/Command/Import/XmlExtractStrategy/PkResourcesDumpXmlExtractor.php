<?php
namespace Repeka\Plugins\Redo\Command\Import\XmlExtractStrategy;

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
            $children = [];
            foreach ($metadata->children() as $childName => $childValue) {
                $children[$childName] = [];
                foreach (current($childValue->attributes()) as $attr => $value) {
                    $children[$childName][$attr] = $value;
                }
            }
            $metadataData = array_merge($children, $metadataData);
            $resourceData[$termId][] = $metadataData;
        }
        return $resourceData;
    }
}
