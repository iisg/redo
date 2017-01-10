<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Metadata;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetadataNormalizer implements NormalizerInterface {
    /**
     * @param $metadata Metadata
     * @inheritdoc
     */
    public function normalize($metadata, $format = null, array $context = []) {
        return [
            'id' => $metadata->getId(),
            'name' => $metadata->getName(),
            'control' => $metadata->getControl(),
            'label' => $metadata->getLabel(),
            'placeholder' => $this->emptyArrayAsObject($metadata->getPlaceholder()),
            'description' => $this->emptyArrayAsObject($metadata->getDescription()),
        ];
    }

    /**
     * Forces to serialize empty array as json object (i.e. {} instead of []).
     */
    private function emptyArrayAsObject(array $array) {
        if (count($array) == 0) {
            return new \stdClass();
        }
        return $array;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Metadata;
    }
}
