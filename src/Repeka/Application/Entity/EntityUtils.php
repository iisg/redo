<?php

namespace Repeka\Application\Entity;

class EntityUtils {
    private function __construct() {
    }

    public static function forceSetId($entity, $id) {
        (new self())->doForceSetId($entity, $id); // just because IDE thinks $this is an error in static methods
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function doForceSetId($entity, $id) {
        $idSetter = function ($id) {
            $this->id = $id;
        };
        $idSetter->call($entity, $id);
    }
}
