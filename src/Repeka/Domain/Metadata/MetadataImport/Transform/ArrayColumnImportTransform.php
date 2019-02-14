<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;
use Repeka\Domain\Utils\ArrayUtils;

class ArrayColumnImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        Assertion::keyExists($config, 'keys');
        $keys = explode(',', $config['keys']);
        $necessaryValues = [];
        Assertion::allIsArray($values);
        foreach ($values as $element) {
            $necessaryCodesForElement = [];
            foreach ($keys as $key) {
                if (isset($element[$key])) {
                    $necessaryCodesForElement[] = $element[$key];
                }
            }
            $necessaryValues[] = ArrayUtils::flatten($necessaryCodesForElement);
        }
        return $necessaryValues;
    }
}
