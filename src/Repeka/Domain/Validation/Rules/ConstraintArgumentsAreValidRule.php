<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Validation\Exceptions\ConstraintArgumentsAreValidRuleException;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\ConfigurableMetadataConstraint;
use Respect\Validation\Rules\AbstractRule;

class ConstraintArgumentsAreValidRule extends AbstractRule {
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    public function __construct(MetadataConstraintManager $metadataConstraintManager) {
        $this->metadataConstraintManager = $metadataConstraintManager;
    }

    public function validate($input): bool {
        try {
            $this->assert($input);
            return true;
        } catch (ConstraintArgumentsAreValidRuleException $e) {
            return false;
        }
    }

    public function assert($input) {
        try {
            Assertion::isArray($input);
            foreach ($input as $constraintName => $constraintArgument) {
                $validator = $this->metadataConstraintManager->get($constraintName);
                if ($validator instanceof ConfigurableMetadataConstraint && !$validator->isConfigValid($constraintArgument)) {
                    throw new \InvalidArgumentException('Invalid metadata constraint.');
                }
            }
        } catch (InvalidCommandException $e) {
            throw $this->reportError(
                $input,
                $e->getViolations()
            );
        } catch (\Exception $e) {
            throw $this->reportError(
                $input,
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
