<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Assert\Assertion;

trait MetadataFixtureTrait {
    protected function constraints(int $maxCount) {
        return [
            'maxCount' => $maxCount,
        ];
    }

    protected function textConstraints(int $maxCount, string $regex = ''): array {
        $constraints = $this->constraints($maxCount);
        $constraints['regex'] = $regex;
        return $constraints;
    }

    protected function relationshipConstraints(int $maxCount, array $resourceKind = []): array {
        Assertion::allInteger($resourceKind);
        $constraints = $this->constraints($maxCount);
        $constraints['resourceKind'] = $resourceKind;
        return $constraints;
    }
}
