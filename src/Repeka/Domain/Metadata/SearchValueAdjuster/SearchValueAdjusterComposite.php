<?php
namespace Repeka\Domain\Metadata\SearchValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;

class SearchValueAdjusterComposite implements SearchValueAdjuster {
    private $controlAdjustersMap = [];
    /** @var SearchValueAdjuster[] */
    private $adjusters;
    /** @var DefaultSearchValueAdjuster */
    private $defaultSearchValueAdjuster;

    /** @param SearchValueAdjuster[] $adjusters */
    public function __construct(iterable $adjusters) {
        $this->adjusters = $adjusters;
    }

    private function buildAdjustersMap() {
        $this->defaultSearchValueAdjuster = new DefaultSearchValueAdjuster();
        foreach (MetadataControl::values() as $control) {
            $this->controlAdjustersMap[$control->getValue()] = $this->defaultSearchValueAdjuster;
            foreach ($this->adjusters as $searchValueAdjuster) {
                if ($searchValueAdjuster->supports($control)) {
                    $this->controlAdjustersMap[$control->getValue()] = $searchValueAdjuster;
                    break;
                }
            }
        }
    }

    public function supports(MetadataControl $control): bool {
        return false;
    }

    public function adjustSearchValue($value, MetadataControl $control) {
        if (!$this->controlAdjustersMap) {
            $this->buildAdjustersMap();
        }
        /** @var SearchValueAdjuster $searchValueAdjuster */
        $searchValueAdjuster = $this->controlAdjustersMap[$control->getValue()];
        return $searchValueAdjuster->adjustSearchValue($value, $control);
    }
}
