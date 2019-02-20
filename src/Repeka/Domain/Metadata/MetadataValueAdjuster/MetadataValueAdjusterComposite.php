<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataValueAdjusterComposite implements MetadataValueAdjuster {
    private $controlAdjustersMap = [];
    /** @var MetadataValueAdjuster[] */
    private $adjusters;
    /** @var DefaultMetadataValueAdjuster */
    private $defaultMetadataValueAdjuster;
    /** @var MetadataRepository */
    private $metadataRepository;

    /** @param MetadataValueAdjuster[] $adjusters */
    public function __construct(iterable $adjusters, MetadataRepository $metadataRepository) {
        $this->adjusters = $adjusters;
        $this->metadataRepository = $metadataRepository;
    }

    private function buildAdjustersMap() {
        $this->defaultMetadataValueAdjuster = new DefaultMetadataValueAdjuster();
        foreach (MetadataControl::toArray() as $control) {
            $this->controlAdjustersMap[$control] = $this->defaultMetadataValueAdjuster;
            foreach ($this->adjusters as $metadataValueAdjuster) {
                if ($metadataValueAdjuster->supports($control)) {
                    $this->controlAdjustersMap[$control] = $metadataValueAdjuster;
                    break;
                }
            }
        }
    }

    public function supports(string $control): bool {
        return false;
    }

    public function adjustAllValuesInContents(ResourceContents $contents): ResourceContents {
        return $contents->mapAllValues(
            function (MetadataValue $value, int $metadataId) {
                $metadata = $this->metadataRepository->findOne($metadataId);
                return $this->adjustMetadataValue($value, $metadata->getControl());
            }
        );
    }

    public function adjustMetadataValue(MetadataValue $value, MetadataControl $control): MetadataValue {
        if (!$this->controlAdjustersMap) {
            $this->buildAdjustersMap();
        }
        /** @var MetadataValueAdjuster $metadataValueAdjuster */
        $metadataValueAdjuster = $this->controlAdjustersMap[$control->getValue()];
        return $metadataValueAdjuster->adjustMetadataValue($value, $control);
    }
}
