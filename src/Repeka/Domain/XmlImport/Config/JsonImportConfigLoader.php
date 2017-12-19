<?php
namespace Repeka\Domain\XmlImport\Config;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\XmlImport\Mapping\Mapping;
use Repeka\Domain\XmlImport\Mapping\MappingLoader;
use Repeka\Domain\XmlImport\Transform\TransformLoader;
use Respect\Validation\Validator;

class JsonImportConfigLoader {
    /** @var TransformLoader */
    private $transformLoader;
    /** @var MappingLoader */
    private $mappingLoader;

    public function __construct(TransformLoader $transformLoader, MappingLoader $mappingLoader) {
        $this->transformLoader = $transformLoader;
        $this->mappingLoader = $mappingLoader;
    }

    public function load(array $configArray, ResourceKind $resourceKind): XmlImportConfig {
        $this->validateConfig($configArray);
        $transforms = $this->transformLoader->load($configArray['transforms']);
        $mappingsResult = $this->mappingLoader->load($configArray['mappings'], $resourceKind);
        $this->assertUsedTransformsExist($mappingsResult->getLoadedMappings(), array_keys($transforms));
        return new XmlImportConfig($transforms, $mappingsResult->getLoadedMappings(), $mappingsResult->getKeysMissingFromResourceKind());
    }

    private function validateConfig($input): void {
        if (!Validator::arrayType()->keySet(
            Validator::key('transforms', null, false),
            Validator::key('mappings', null, false)
        )->validate($input)) {
            throw new InvalidTopLevelKeysException();
        }
    }

    /**
     * @param Mapping[] $mappings
     * @param string[] $transformNames
     */
    private function assertUsedTransformsExist(array $mappings, array $transformNames): void {
        $usedTransforms = [];
        foreach ($mappings as $mapping) {
            $usedTransforms = array_merge($usedTransforms, $mapping->getExpression()->getRequiredTransformNames());
        }
        $usedTransforms = array_values(array_unique($usedTransforms));
        $missingTransforms = array_diff($usedTransforms, $transformNames);
        if (!empty($missingTransforms)) {
            throw new MissingTransformsException($missingTransforms);
        }
    }
}
