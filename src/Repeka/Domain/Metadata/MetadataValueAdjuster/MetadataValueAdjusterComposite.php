<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

class MetadataValueAdjusterComposite implements MetadataValueAdjuster {
    private $controlAdjustersMap = [];
    /** @var MetadataValueAdjuster[] */
    private $adjusters;
    /** @var DefaultMetadataValueAdjuster */
    private $defaultMetadataValueAdjuster;

    /** @param MetadataValueAdjuster[] $adjusters */
    public function __construct(iterable $adjusters) {
        $this->adjusters = $adjusters;
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

    public function adjustMetadataValue(MetadataValue $value, MetadataControl $control): MetadataValue {
        if (!$this->controlAdjustersMap) {
            $this->buildAdjustersMap();
        }
        /** @var MetadataValueAdjuster $metadataValueAdjuster */
        $metadataValueAdjuster = $this->controlAdjustersMap[$control->getValue()];
        return $metadataValueAdjuster->adjustMetadataValue($value, $control);
    }
}
