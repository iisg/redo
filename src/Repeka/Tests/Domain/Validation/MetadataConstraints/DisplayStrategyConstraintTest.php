<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Validation\MetadataConstraints\DisplayStrategyConstraint;
use Repeka\Tests\Traits\StubsTrait;

class DisplayStrategyConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceDisplayStrategyEvaluator */
    private $compiler;
    /** @var DisplayStrategyConstraint */
    private $constraint;

    /** @before */
    public function prepare() {
        $this->compiler = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->constraint = new DisplayStrategyConstraint($this->compiler);
    }

    public function testValid() {
        $this->assertTrue($this->constraint->isConfigValid('aa'));
    }

    public function testInvalid() {
        $this->expectException(InvalidResourceDisplayStrategyException::class);
        $this->expectExceptionMessage('ERROR!');
        $this->compiler
            ->method('validateTemplate')
            ->with('aa')
            ->willThrowException(new InvalidResourceDisplayStrategyException('ERROR!'));
        $this->constraint->isConfigValid('aa');
    }

    public function testInvalidWhenEmpty() {
        $this->assertFalse($this->constraint->isConfigValid(''));
    }

    public function testInvalidWhenNotString() {
        $this->assertFalse($this->constraint->isConfigValid([]));
    }
}
