<?php
namespace Repeka\Tests\Traits;

use Repeka\Domain\Factory\ResourceContentsNormalizer;

trait ResourceContentsNormalizerAware {
    protected function normalizeContents(array $contents): array {
        $normalizer = new ResourceContentsNormalizer();
        return $normalizer->normalize($contents);
    }
}
