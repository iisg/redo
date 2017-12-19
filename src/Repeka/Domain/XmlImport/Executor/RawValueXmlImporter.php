<?php
namespace Repeka\Domain\XmlImport\Executor;

use Repeka\Domain\XmlImport\Config\XmlImportConfig;
use Repeka\Domain\XmlImport\Expression\Subfield;
use Repeka\Domain\XmlImport\Mapping\Mapping;
use Symfony\Component\DomCrawler\Crawler;

class RawValueXmlImporter {
    /**
     * @return string[][] with metadata base ID keys
     */
    public function import(string $xml, XmlImportConfig $config): array {
        $crawler = new Crawler($xml);
        $values = [];
        foreach ($config->getMappings() as $mapping) {
            $matchedElements = $crawler->filter($mapping->getCssSelector());
            /** @var string[] $mappingValues */
            $mappingValues = [];
            foreach ($matchedElements as $element) {
                $subfields = [];
                foreach ($element->childNodes as $subfieldNode) {
                    /** @var \DOMNode $subfieldNode */
                    if ($subfieldNode->nodeType === XML_ELEMENT_NODE) {
                        $subfields[] = Subfield::fromDOMNode($subfieldNode);
                    }
                }
                if (empty($subfields)) {
                    $subfields = [new Subfield('*', $element->textContent)];
                }
                $this->assertRequiredSubfieldsExist($subfields, $mapping);
                $elementValues = $mapping->getExpression()->evaluate($subfields, $config->getTransforms());
                $mappingValues = array_merge($mappingValues, $elementValues);
            }
            $values[$mapping->getMetadata()->getBaseId()] = $mappingValues;
        }
        return $values;
    }

    /**
     * @param Subfield[] $subfields
     */
    private function assertRequiredSubfieldsExist(array $subfields, Mapping $mapping): void {
        $availableSubfieldNames = array_unique(array_map(function (Subfield $subfield) {
            return $subfield->getName();
        }, $subfields));
        $missingSubfields = array_diff($mapping->getExpression()->getRequiredSubfieldNames(), $availableSubfieldNames);
        if (!empty($missingSubfields)) {
            throw new MissingSubfieldsException($mapping->getConfigKey(), $missingSubfields);
        }
    }
}
