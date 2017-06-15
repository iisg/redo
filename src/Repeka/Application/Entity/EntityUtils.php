<?php
namespace Repeka\Application\Entity;

class EntityUtils {
    public static function forceSetId($entity, $id) {
        (new self())->forceSetId_($entity, $id); // just because IDE thinks $this is an error in static methods
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    // @codingStandardsIgnoreStart
    private function forceSetId_($entity, $id) {
        $idSetter = function ($id) {
            $this->id = $id;
        };
        $idSetter->call($entity, $id);
    }
    // @codingStandardsIgnoreEnd
}
