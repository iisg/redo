<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

/**
 * @method object getReference(string $referenceName)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
trait ResourceKindsFixtureTrait {
    private function metadata(string $baseReference, ?int $maxCount = 0, bool $shownInBrief = false): array {
        $metadata = [
            'baseId' => $this->getReference($baseReference)->getId(),
            'shownInBrief' => $shownInBrief,
        ];
        if ($maxCount != null) {
            $metadata['constraints'] = ['maxCount' => $maxCount];
        }
        return $metadata;
    }
}
