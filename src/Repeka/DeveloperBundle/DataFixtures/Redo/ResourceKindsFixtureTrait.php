<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Repeka\Domain\Entity\Metadata;

/**
 * @method object getReference(string $referenceName)
 */
trait ResourceKindsFixtureTrait {
    private function metadata(string $baseReference): Metadata {
        return $this->getReference($baseReference);
    }
}
