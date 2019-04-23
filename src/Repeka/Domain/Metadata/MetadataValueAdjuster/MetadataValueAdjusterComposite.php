<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataValueAdjusterComposite implements MetadataValueAdjuster {
    private $controlAdjustersMap = [];
    /** @var MetadataValueAdjuster[][] */
    private $adjusters;
    /** @var MetadataRepository */
    private $metadataRepository;

    /** @param MetadataValueAdjuster[] $adjusters */
    public function __construct(iterable $adjusters, MetadataRepository $metadataRepository) {
        $this->adjusters = $adjusters;
        $this->metadataRepository = $metadataRepository;
    }

    private function buildAdjustersMap() {
        foreach (MetadataControl::toArray() as $control) {
            foreach ($this->adjusters as $metadataValueAdjuster) {
                if ($metadataValueAdjuster->supports($control)) {
                    $this->controlAdjustersMap[$control][] = $metadataValueAdjuster;
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
                return $this->adjustMetadataValue($value, $metadata);
            }
        );
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        if (!$this->controlAdjustersMap) {
            $this->buildAdjustersMap();
        }
        $adjusters = $this->controlAdjustersMap[$metadata->getControl()->getValue()] ?? [];
        foreach ($adjusters as $adjuster) {
            $value = $adjuster->adjustMetadataValue($value, $metadata);
        }
        return $value;
    }
}
