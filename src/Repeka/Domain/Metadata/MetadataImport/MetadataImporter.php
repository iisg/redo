<?php
namespace Repeka\Domain\Metadata\MetadataImport;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfig;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Domain\Metadata\MetadataImport\Transform\ImportTransformComposite;

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
                    $valuesBasedOnImportKey = [];
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
                $transformedValues = $this->transforms->apply($transformedValues, $transformConfig, $data);
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
            $allMetadataValues[$metadata->getId()] = array_merge($allMetadataValues[$metadata->getId()] ?? [], $metadataValues);
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
                return $this->transformIntegerValue($resultBuilder, $id, $value);
            case MetadataControl::DOUBLE:
                return $this->transformDoubleValue($resultBuilder, $id, $value);
            case MetadataControl::BOOLEAN:
                return $this->transformBooleanValue($resultBuilder, $id, $value);
            case MetadataControl::TIMESTAMP:
            case MetadataControl::FLEXIBLE_DATE:
                return $this->transformDateValue($resultBuilder, $id, $value);
            default:
                $resultBuilder->addUnfitTypeValues($id, $value);
                return null;
        }
    }

    private function transformIntegerValue(ImportResultBuilder &$resultBuilder, int $id, string $value) {
        $value = $this->transformDoubleValue($resultBuilder, $id, $value);
        return $value ? round($value) : $value;
    }

    private function transformDoubleValue(ImportResultBuilder &$resultBuilder, int $id, string $value) {
        if (preg_match('/^\d/', $value)) {
            return floatval(str_replace(',', '.', $value));
        } else {
            $resultBuilder->addUnfitTypeValues($id, $value);
            return null;
        }
    }

    private function transformBooleanValue(ImportResultBuilder &$resultBuilder, int $id, string $value) {
        if (preg_match('/^(1|true)$/', $value)) {
            return true;
        } elseif (preg_match('/^(0|false|)$/', $value)) {
            return false;
        } else {
            $resultBuilder->addUnfitTypeValues($id, $value);
            return null;
        }
    }

    private function transformDateValue(ImportResultBuilder $resultBuilder, int $id, string $value) {
        if ($time = strtotime($value)) {
            return date('Y-m-d', $time);
        } else {
            $resultBuilder->addUnfitTypeValues($id, $value);
            return null;
        }
    }
}
