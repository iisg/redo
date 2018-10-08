<?php
namespace Repeka\Domain\MetadataImport\Config;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\MetadataImport\Mapping\MappingLoader;
use Repeka\Domain\MetadataImport\Transform\TransformLoader;
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
        if (preg_match('#.ya?ml$#', $configPath)) {
            $yamlParser = new Parser();
            $configuration = $yamlParser->parseFile($configPath, Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS);
            return $this->fromArray($configuration, $resourceKind);
        } else {
            return $this->fromString(file_get_contents($configPath), $resourceKind);
        }
    }

    public function fromString(string $config, ResourceKind $resourceKind): ImportConfig {
        $jsonConfig = json_decode($config, true);
        Assertion::isArray($jsonConfig, 'Invalid import config. ' . json_last_error_msg());
        return $this->fromArray($jsonConfig, $resourceKind);
    }

    public function fromArray(array $configArray, ResourceKind $resourceKind): ImportConfig {
        Assertion::notEmptyKey($configArray, 'mappings', 'No mappings found in the config.');
        $mappingsResult = $this->mappingLoader->load($configArray['mappings'], $resourceKind);
        return new ImportConfig($mappingsResult->getLoadedMappings(), $mappingsResult->getKeysMissingFromResourceKind());
    }
}
