<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommandValidator;
use Repeka\Domain\Validation\Rules\ResourceHasNoChildrenRule;

class ResourceDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceHasNoChildrenRule|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceHasNoChildrenRule;
    /** @var ResourceDeleteCommandValidator */
    private $validator;
    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $this->user = $this->createMock(User::class);
        $this->resourceHasNoChildrenRule = $this->createMock(ResourceHasNoChildrenRule::class);
        $this->validator = new ResourceDeleteCommandValidator($this->resourceHasNoChildrenRule);
    }

    public function testPositive() {
        $this->resourceHasNoChildrenRule->expects($this->once())->method('validate')->willReturn(true);
        $command = new ResourceDeleteCommand($this->createMock(ResourceEntity::class), $this->user);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testNegative() {
        $this->resourceHasNoChildrenRule->expects($this->once())->method('validate')->willReturn(false);
        $command = new ResourceDeleteCommand($this->createMock(ResourceEntity::class), $this->user);
        $this->assertFalse($this->validator->isValid($command));
    }
}
