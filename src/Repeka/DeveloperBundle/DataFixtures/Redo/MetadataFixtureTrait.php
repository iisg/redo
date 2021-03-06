<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Assert\Assertion;
use Repeka\Domain\Constants\FileUploaderType;

trait MetadataFixtureTrait {
    protected function constraints(int $maxCount) {
        return [
            'maxCount' => $maxCount,
        ];
    }

    protected function textConstraints($maxCount, string $regex = ''): array {
        if (isset($maxCount)) {
            $constraints = $this->constraints($maxCount);
        }
        $constraints['regex'] = $regex;
        return $constraints;
    }

    protected function relationshipConstraints($maxCount, array $resourceKind = []): array {
        Assertion::allInteger($resourceKind);
        if (isset($maxCount)) {
            $constraints = $this->constraints($maxCount);
        }
        $constraints['resourceKind'] = $resourceKind;
        return $constraints;
    }

    protected function fileConstraint($maxCount, FileUploaderType $uploader) {
        if (isset($maxCount)) {
            $constraints = $this->constraints($maxCount);
        }
        $constraints['fileUploaderType'] = $uploader->getValue();
        return $constraints;
    }
}
