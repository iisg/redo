<?php
namespace Repeka\Application\Serialization;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface {
    /**
     * Forces to serialize empty array as json object (i.e. {} instead of []).
     */
    protected function emptyArrayAsObject(array $array) {
        if (count($array) == 0) {
            return new \stdClass();
        }
        return $array;
    }
}
