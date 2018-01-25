<?php
namespace Repeka\Domain\MetadataImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Factory\ResourceContentsNormalizer;
use Repeka\Domain\MetadataImport\Config\ImportConfig;
use Repeka\Domain\MetadataImport\Transform\ImportTransformComposite;

class MetadataImporter {
    /** @var ImportTransformComposite */
    private $transforms;
    /** @var ResourceContentsNormalizer */
    private $resourceContentsNormalizer;

    public function __construct(ImportTransformComposite $transforms, ResourceContentsNormalizer $resourceContentsNormalizer) {
        $this->transforms = $transforms;
        $this->resourceContentsNormalizer = $resourceContentsNormalizer;
    }

    public function import(array $data, ImportConfig $config): ImportResult {
        $resultBuilder = new ImportResultBuilder($config->getInvalidMetadataKeys());
        foreach ($config->getMappings() as $mapping) {
            if (isset($data[$mapping->getImportKey()])) {
                $values = $data[$mapping->getImportKey()];
            } else {
                continue;
            }
            if (!is_array($values)) {
                $values = [$values];
            }
            foreach ($mapping->getTransformsConfig() as $transformConfig) {
                $values = $this->transforms->apply($values, $transformConfig);
            }
            $metadata = $mapping->getMetadata();
            $id = $metadata->getId();
            switch ($metadata->getControl()->getValue()) {
                case MetadataControl::TEXT:
                case MetadataControl::TEXTAREA:
                    $resultBuilder->addAcceptedValues($id, $values);
                    break;
                case MetadataControl::INTEGER:
                    $this->addIntegerValues($resultBuilder, $id, $values);
                    break;
                case MetadataControl::BOOLEAN:
                    $this->addBooleanValues($resultBuilder, $id, $values);
                    break;
                default:
                    $resultBuilder->addUnfitTypeValues($id, $values);
            }
        }
        return $resultBuilder->build($this->resourceContentsNormalizer);
    }

    /**
     * @param string[] $metadataValues
     */
    private function addIntegerValues(ImportResultBuilder $resultBuilder, int $id, array $metadataValues) {
        $accepted = [];
        $rejected = [];
        foreach ($metadataValues as $value) {
            if (preg_match('/^\d+$/', $value)) {
                $accepted[] = intval($value);
            } else {
                $rejected[] = $value;
            }
        }
        $resultBuilder->addAcceptedValues($id, $accepted);
        $resultBuilder->addUnfitTypeValues($id, $rejected);
    }

    /**
     * @param string[] $metadataValues
     */
    private function addBooleanValues(ImportResultBuilder $resultBuilder, int $id, array $metadataValues) {
        $accepted = [];
        $rejected = [];
        foreach ($metadataValues as $value) {
            if (preg_match('/^(1|true)$/', $value)) {
                $accepted[] = true;
            } elseif (preg_match('/^(0|false|)$/', $value)) {
                $accepted[] = false;
            } else {
                $rejected[] = $value;
            }
        }
        $resultBuilder->addAcceptedValues($id, $accepted);
        $resultBuilder->addUnfitTypeValues($id, $rejected);
    }
}
