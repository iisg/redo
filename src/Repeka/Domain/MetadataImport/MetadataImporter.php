<?php
namespace Repeka\Domain\MetadataImport;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\MetadataImport\Config\ImportConfig;
use Repeka\Domain\MetadataImport\Mapping\Mapping;
use Repeka\Domain\MetadataImport\Transform\ImportTransformComposite;

class MetadataImporter {
    /** @var ImportTransformComposite */
    private $transforms;

    public function __construct(ImportTransformComposite $transforms) {
        $this->transforms = $transforms;
    }

    public function import(array $data, ImportConfig $config): ImportResult {
        $resultBuilder = new ImportResultBuilder($config->getInvalidMetadataKeys());
        $resourceContents = $this->importMetadataValues($data, $config->getMappings(), $resultBuilder);
        $resultBuilder->setAcceptedValues($resourceContents);
        return $resultBuilder->build();
    }

    public function importMetadataValues($data, array $mappings, ImportResultBuilder $resultBuilder): array {
        $allMetadataValues = [];
        foreach ($mappings as $mapping) {
            /** @var Mapping $mapping */
            if ($mapping->getImportKey()) {
                if (isset($data[$mapping->getImportKey()])) {
                    $valuesBasedOnImportKey = $data[$mapping->getImportKey()];
                } else {
                    continue;
                }
            } else {
                $valuesBasedOnImportKey = [$data];
            }
            if (!is_array($valuesBasedOnImportKey)) {
                $valuesBasedOnImportKey = [$valuesBasedOnImportKey];
            }
            $metadataValues = [];
            $metadata = $mapping->getMetadata();
            $transformedValues = $valuesBasedOnImportKey;
            foreach ($mapping->getTransformsConfig() as $transformConfig) {
                $transformedValues = $this->transforms->apply($transformedValues, $transformConfig);
            }
            for ($i = 0; $i < count($transformedValues); $i++) {
                $transformedValue = $this->prepareMetadataValues($resultBuilder, $metadata, $transformedValues[$i]);
                if ($transformedValue !== null) {
                    $metadataValue = ['value' => $transformedValue];
                    if ($mapping->getSubmetadataMappings()) {
                        $submetadata = $this->importMetadataValues(
                            $valuesBasedOnImportKey[$i],
                            $mapping->getSubmetadataMappings(),
                            $resultBuilder
                        );
                        $metadataValue['submetadata'] = $submetadata;
                    }
                    $metadataValues[] = $metadataValue;
                }
            }
            $allMetadataValues[$metadata->getId()] = $metadataValues;
        }
        return $allMetadataValues;
    }

    private function prepareMetadataValues(ImportResultBuilder &$resultBuilder, Metadata $metadata, string $value) {
        $id = $metadata->getId();
        switch ($metadata->getControl()->getValue()) {
            case MetadataControl::TEXT:
            case MetadataControl::TEXTAREA:
                return $value;
            case MetadataControl::INTEGER:
            case MetadataControl::RELATIONSHIP:
                return $this->transformIntegerValues($resultBuilder, $id, $value);
            case MetadataControl::BOOLEAN:
                return $this->transformBooleanValues($resultBuilder, $id, $value);
            default:
                $resultBuilder->addUnfitTypeValues($id, $value);
                return null;
        }
    }

    /**
     * @param string[] $metadataValues
     */
    private function transformIntegerValues(ImportResultBuilder &$resultBuilder, int $id, string $value) {
        if (preg_match('/^\d+$/', $value)) {
            return intval($value);
        } else {
            $resultBuilder->addUnfitTypeValues($id, $value);
            return null;
        }
    }

    /**
     * @param string[] $metadataValues
     */
    private function transformBooleanValues(ImportResultBuilder &$resultBuilder, int $id, string $value) {
        if (preg_match('/^(1|true)$/', $value)) {
            return true;
        } elseif (preg_match('/^(0|false|)$/', $value)) {
            return false;
        } else {
            $resultBuilder->addUnfitTypeValues($id, $value);
            return null;
        }
    }
}
