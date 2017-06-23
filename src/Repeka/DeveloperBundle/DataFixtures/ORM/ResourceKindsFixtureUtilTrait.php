<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

/**
 * @method object getReference(string $referenceName)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
trait ResourceKindsFixtureUtilTrait {
    private function metadata(string $baseReference, bool $shownInBrief = false): array {
        return [
            'baseId' => $this->getReference($baseReference)->getId(),
            'shownInBrief' => $shownInBrief,
        ];
    }
}
