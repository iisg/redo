<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Repeka\Domain\Entity\Metadata;

/**
 * @method object getReference(string $referenceName)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
trait ResourceKindsFixtureTrait {
    private function metadata(string $baseReference, ?int $maxCount = 0, bool $shownInBrief = false): Metadata {
        /** @var Metadata $metadata */
        $metadata = $this->getReference($baseReference);
        $metadata->setOverrides([
            'constraints' => ['maxCount' => $maxCount],
            'shownInBrief' => $shownInBrief,
        ]);
        return $metadata;
    }
}
