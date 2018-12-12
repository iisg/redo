<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

class FileAndDirectoryMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::FILE || $control == MetadataControl::DIRECTORY;
    }

    public function adjustMetadataValue(MetadataValue $value, MetadataControl $control): MetadataValue {
        $path = urldecode($value->getValue());
        $path = str_replace('\\', '/', $path);
        return $value->withNewValue($path);
    }
}
