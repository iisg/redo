<?php
namespace Repeka\Domain\XmlImport\Executor;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\XmlImport\Config\XmlImportConfig;

class XmlImportExecutor {
    /** @var RawValueXmlImporter */
    private $rawValueXmlImporter;

    public function __construct(RawValueXmlImporter $rawValueXmlImporter) {
        $this->rawValueXmlImporter = $rawValueXmlImporter;
    }

    public function execute(string $xml, XmlImportConfig $config, ResourceKind $resourceKind): ImportResult {
        $values = $this->rawValueXmlImporter->import($xml, $config);
        $resultBuilder = new ImportResultBuilder($config->getInvalidMetadataKeys());
        foreach ($resourceKind->getMetadataList() as $metadata) {
            $id = $metadata->getBaseId();
            if (!array_key_exists($id, $values)) {
                continue;
            }
            $metadataValues = $values[$id];
            switch ($metadata->getControl()->getValue()) {
                case MetadataControl::TEXT:
                case MetadataControl::TEXTAREA:
                    $resultBuilder->addAcceptedValues($id, $metadataValues);
                    break;
                case MetadataControl::INTEGER:
                    $this->addIntegerValues($resultBuilder, $id, $metadataValues);
                    break;
                case MetadataControl::BOOLEAN:
                    $this->addBooleanValues($resultBuilder, $id, $metadataValues);
                    break;
                default:
                    $resultBuilder->addUnfitTypeValues($id, $metadataValues);
            }
        }
        return $resultBuilder->build();
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
