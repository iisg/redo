<?php
namespace Repeka\Domain\Metadata\MetadataImport\Xml;

use Symfony\Component\DomCrawler\Crawler;

class XmlArrayDataExtractor {
    public function import(array $mappings, string $xmlString): array {
        $crawler = new Crawler($xmlString);
        $importedValues = [];
        foreach ($mappings as $metadataKey => $selector) {
            $importedValues[$metadataKey] = [];
            $crawler->filter($selector)->each(
                function (Crawler $subtree) use (&$importedValues, $metadataKey) {
                    $importedValues[$metadataKey][] = $subtree->html();
                }
            );
        }
        return $importedValues;
    }
}
