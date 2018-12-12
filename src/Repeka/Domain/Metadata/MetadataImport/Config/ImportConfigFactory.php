<?php
namespace Repeka\Domain\Metadata\MetadataImport\Config;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataImport\Mapping\MappingLoader;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class ImportConfigFactory {
    /** @var MappingLoader */
    private $mappingLoader;

    public function __construct(MappingLoader $mappingLoader) {
        $this->mappingLoader = $mappingLoader;
    }

    public function fromFile(string $configPath, ResourceKind $resourceKind): ImportConfig {
        Assertion::file($configPath);
        return $this->fromString(file_get_contents($configPath), $resourceKind);
    }

    public function fromString(string $config, ResourceKind $resourceKind): ImportConfig {
        if (trim($config){0} === '{') {
            $config = json_decode($config, true);
        } else {
            $config = YAML::parse($config);
        }
        Assertion::isArray($config, 'Invalid import config. ' . json_last_error_msg());
        return $this->fromArray($config, $resourceKind);
    }

    public function fromArray(array $configArray, ResourceKind $resourceKind): ImportConfig {
        Assertion::notEmptyKey($configArray, 'mappings', 'No mappings found in the config.');
        $mappingsResult = $this->mappingLoader->load($configArray['mappings'], $resourceKind);
        return new ImportConfig($mappingsResult->getLoadedMappings(), $mappingsResult->getKeysMissingFromResourceKind());
    }
}
