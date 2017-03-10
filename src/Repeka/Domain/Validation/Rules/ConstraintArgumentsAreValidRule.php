<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Validation\MetadataConstraintProvider;
use Respect\Validation\Rules\AbstractRule;

class ConstraintArgumentsAreValidRule extends AbstractRule {
    /** @var MetadataConstraintProvider */
    private $metadataConstraintProvider;

    public function __construct(MetadataConstraintProvider $metadataConstraintProvider) {
        $this->metadataConstraintProvider = $metadataConstraintProvider;
    }

    public function validate($input) {
        Assertion::isArray($input);
        foreach ($input as $constraintName => $constraintArgument) {
            try {
                $validator = $this->metadataConstraintProvider->get($constraintName);
            } catch (\InvalidArgumentException $e) {
                return false;
            }
            if (!$validator->validateArgument($constraintArgument)) {
                return false;
            }
        }
        return true;
    }
}
