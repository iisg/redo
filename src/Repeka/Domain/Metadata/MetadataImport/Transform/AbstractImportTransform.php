<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Stringy\StaticStringy;

/** @SuppressWarnings("PHPMD.NumberOfChildren") */
abstract class AbstractImportTransform implements ImportTransform {
    public function getName(): string {
        $reflectionClass = new \ReflectionClass($this);
        $withoutSuffix = preg_replace("/ImportTransform/", '', $reflectionClass->getShortName());
        return StaticStringy::camelize($withoutSuffix);
    }
}
