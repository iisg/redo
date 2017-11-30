<?php
namespace Repeka\Tests\Application\Service;

use Repeka\Application\Service\LightnCandyHandlebarsResourceDisplayStrategyEvaluator;
use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;

class LightnCandyHandlebarsResourceDisplayStrategyEvaluatorTest extends \PHPUnit_Framework_TestCase {
    /** @var LightnCandyHandlebarsResourceDisplayStrategyEvaluator */
    private $evaluator;

    /** @before */
    public function initCompiler() {
        $this->evaluator = new LightnCandyHandlebarsResourceDisplayStrategyEvaluator();
    }

    public function testDetectingInvalidSyntaxInTemplate() {
        $this->expectException(InvalidResourceDisplayStrategyException::class);
        $this->evaluator->validateTemplate('{{{a}}');
    }

    public function testRecognizingCorrectSyntaxInTemplate() {
        $this->evaluator->validateTemplate('Hello {{a}}');
    }
}
