<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Respect\Validation\Rules\AbstractRule;

class ResourceDisplayStrategySyntaxValidRule extends AbstractRule {
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    public function validate($input) {
        try {
            if ($input) {
                $this->displayStrategyEvaluator->validateTemplate($input);
            }
            return true;
        } catch (InvalidResourceDisplayStrategyException $e) {
            throw $this->reportError($input, ['message' => $e->getErrorMessage()]);
        }
    }
}
