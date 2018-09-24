<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Domain\Repository\MetadataRepository;

class ESContentsAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function adjustContents($contents): array {
        $adjustedContents = [];
        foreach ($contents as $key => $values) {
            $adjustedMetadata = [];
            $metadata = $this->metadataRepository->findOne($key);
            $control = $metadata->getControl();
            foreach ($values as $value) {
                $singleMetadata = [];
                if (!in_array($control, ResourceConstants::UNACCEPTABLE_TYPES)) {
                    $singleMetadata['value_' . $control] = $value['value'];
                }
                if (isset($value['submetadata'])) {
                    $singleMetadata['submetadata'] = $this->adjustContents($value['submetadata']);
                }
                if (!empty($singleMetadata)) {
                    $adjustedMetadata[] = $singleMetadata;
                }
            }
            if (!empty($adjustedMetadata)) {
                $adjustedContents[$key] = $adjustedMetadata;
            }
        }
        return $adjustedContents;
    }
}
