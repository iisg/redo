<?php
namespace Repeka\Domain\Factory;

class ResourceContentsNormalizer {
    public function normalize(array $contents): array {
        return array_map(function ($metadataEntry) {
            if (is_array($metadataEntry)) {
                return array_map(function ($metadataValue) {
                    if (is_array($metadataValue)) {
                        if (isset($metadataValue['submetadata'])) {
                            $metadataValue['submetadata'] = $this->normalize($metadataValue['submetadata']);
                        }
                        if (!isset($metadataValue['value'])) {
                            $metadataValue['value'] = null;
                        }
                        return $metadataValue;
                    } else {
                        return ['value' => $metadataValue];
                    }
                }, $metadataEntry);
            } else {
                return [['value' => $metadataEntry]];
            }
        }, $contents);
    }
}
