<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Tests\Traits\StubsTrait;

class CorrectResourceDisplayStrategySyntaxRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceDisplayStrategyEvaluator */
    private $compiler;
    /** @var CorrectResourceDisplayStrategySyntaxRule */
    private $rule;

    /** @before */
    public function prepare() {
        $this->compiler = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->rule = new CorrectResourceDisplayStrategySyntaxRule($this->compiler);
    }

    public function testValid() {
        $this->assertTrue($this->rule->validate('aa'));
    }

    public function testInvalid() {
        $this->compiler
            ->method('validateTemplate')
            ->with('aa')
            ->willThrowException(new InvalidResourceDisplayStrategyException('ERROR!'));
        $this->assertFalse($this->rule->validate('aa'));
    }
}
