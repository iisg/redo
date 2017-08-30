<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class ConstraintArgumentsAreValidRule extends AbstractRule {
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    public function __construct(MetadataConstraintManager $metadataConstraintManager) {
        $this->metadataConstraintManager = $metadataConstraintManager;
    }

    public function validate($input) {
        Assertion::isArray($input);
        foreach ($input as $constraintName => $constraintArgument) {
            try {
                $validator = $this->metadataConstraintManager->get($constraintName);
            } catch (\InvalidArgumentException $e) {
                return false;
            }
            if (!$validator->isConfigValid($constraintArgument)) {
                return false;
            }
        }
        return true;
    }
}
