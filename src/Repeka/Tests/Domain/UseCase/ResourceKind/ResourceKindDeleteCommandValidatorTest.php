<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommandValidator;
use Repeka\Domain\Validation\Rules\NoResourcesOfKindExistRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class ResourceKindDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var NoResourcesOfKindExistRule|\PHPUnit_Framework_MockObject_MockObject */
    private $noResourcesOfKindExistRule;
    /** @var ResourceKindDeleteCommand */
    private $command;
    /** @var ResourceKindDeleteCommandValidator */
    private $validator;

    protected function setUp() {
        $this->noResourcesOfKindExistRule = $this->createMock(NoResourcesOfKindExistRule::class);
        $this->command = new ResourceKindDeleteCommand($this->createMock(ResourceKind::class));
        $this->validator = new ResourceKindDeleteCommandValidator($this->noResourcesOfKindExistRule);
    }

    public function testAccepting() {
        $this->noResourcesOfKindExistRule->method('validate')->willReturn(true);
        $this->assertTrue($this->validator->isValid($this->command));
    }

    public function testRejecting() {
        $this->expectException(InvalidCommandException::class);
        $this->noResourcesOfKindExistRule->method('validate')->willReturn(false);
        $this->noResourcesOfKindExistRule->method('assert')->willThrowException(new ValidationException());
        $this->validator->validate($this->command);
    }
}
