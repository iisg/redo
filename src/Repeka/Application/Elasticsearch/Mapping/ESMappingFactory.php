<?php

namespace Repeka\Application\Elasticsearch\Mapping;

use Repeka\Domain\Repository\LanguageRepository;

class ESMappingFactory {
    /** @var int */
    private $nestingDepth;
    /** @var array */
    private $analyzerNames;
    /** @var string[] */
    private $metadataClasses;
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(int $nestingDepth, LanguageRepository $languageRepository, array $analyzerNames, array $metadataClasses) {
        $this->nestingDepth = $nestingDepth;
        $this->analyzerNames = $analyzerNames;
        $this->metadataClasses = $metadataClasses;
        $this->languageRepository = $languageRepository;
    }

    public function getMappingArray(): array {
        $mergedLanguages = $this->getLanguagesMergedWithAnalyzers();
        $properties = [];
        foreach ($this->metadataClasses as $metadataClass) {
            $requiredByMetadata = $metadataClass::getRequiredMapping($mergedLanguages);
            foreach ($requiredByMetadata as $fieldName => $spec) {
                if (array_key_exists($fieldName, $properties) && $properties[$fieldName] != $spec) {
                    throw new \Exception(
                        "Field '$fieldName' required by class $metadataClass already declared with conflicting specification"
                    );
                }
                $properties[$fieldName] = $spec;
            }
        }
        $metadataMapping = [
            'type' => 'nested',
            'properties' => $properties,
        ];
        $metadataMappingTree = $metadataMapping;
        // We have a one-level-deep tree, now add (depth-1) more levels.
        // Note that PHP deep-copies arrays on assignment.
        for ($i = 1; $i < $this->nestingDepth; $i++) {
            $newTopLevel = $metadataMapping;
            $newTopLevel['properties'][ResourceConstants::CHILDREN] = $metadataMappingTree;
            $metadataMappingTree = $newTopLevel;
        }
        return [ResourceConstants::CHILDREN => $metadataMappingTree];
    }

    private function getLanguagesMergedWithAnalyzers() {
        $languages = array_map('strtolower', $this->languageRepository->getAvailableLanguageCodes());
        $languageKeys = array_fill_keys($languages, null); // flips array, but sets values to null
        return array_merge($languageKeys, $this->analyzerNames);
    }
}
