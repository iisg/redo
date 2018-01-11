<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Stringy\StaticStringy;

abstract class AbstractImportTransform implements ImportTransform {
    public function getName(): string {
        $reflectionClass = new \ReflectionClass($this);
        $withoutSuffix = preg_replace("/ImportTransform/", '', $reflectionClass->getShortName());
        return StaticStringy::camelize($withoutSuffix);
    }
}
