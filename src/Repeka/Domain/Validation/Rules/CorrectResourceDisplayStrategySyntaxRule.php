<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Validation\Exceptions\CorrectResourceDisplayStrategySyntaxRuleException;
use Respect\Validation\Rules\AbstractRule;

class CorrectResourceDisplayStrategySyntaxRule extends AbstractRule {
    /** @var ResourceDisplayStrategyEvaluator */
    private $compiler;

    public function __construct(ResourceDisplayStrategyEvaluator $compiler) {
        $this->compiler = $compiler;
    }

    public function validate($input): bool {
        try {
            $this->assert($input);
            return true;
        } catch (CorrectResourceDisplayStrategySyntaxRuleException $e) {
            return false;
        }
    }

    public function assert($input) {
        try {
            $this->compiler->validateTemplate($input);
            return true;
        } catch (InvalidResourceDisplayStrategyException $e) {
            throw $this->reportError($input, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
