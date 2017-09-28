<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Labeled;

abstract class LabeledNormalizer extends AbstractNormalizer {
    /**
     * @param Labeled $place
     * @inheritdoc
     */
    public function normalize($place, $format = null, array $context = []) {
        $data = $place->toArray();
        $data['label'] = $this->emptyArrayAsObject($place->getLabel());
        return $data;
    }
}
