<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceContentsAdjuster {
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository, MetadataValueAdjusterComposite $metadataValueAdjuster) {
        $this->metadataRepository = $metadataRepository;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
    }

    /**
     * @param ResourceContents|array $contents
     * @return ResourceContents
     */
    public function adjust($contents): ResourceContents {
        $contents = is_array($contents) ? ResourceContents::fromArray($contents) : $contents;
        $contents = $contents
            ->filterOutEmptyMetadata()
            ->withMetadataNamesMappedToIds($this->metadataRepository);
        $contents = $this->metadataValueAdjuster->adjustAllValuesInContents($contents);
        $contents = $this->adjustTeaserVisibilityValuesToFullVisibility($contents);
        return $contents;
    }

    private function adjustTeaserVisibilityValuesToFullVisibility(ResourceContents $resourceContents): ResourceContents {
        $fullVisibility = $resourceContents->getValuesWithoutSubmetadata(SystemMetadata::VISIBILITY);
        $teaserVisibility = $resourceContents->getValuesWithoutSubmetadata(SystemMetadata::TEASER_VISIBILITY);
        $adjustedTeaserVisibility = array_merge(array_diff($fullVisibility, $teaserVisibility), $teaserVisibility);
        return $resourceContents->withReplacedValues(SystemMetadata::TEASER_VISIBILITY, $adjustedTeaserVisibility);
    }
}
