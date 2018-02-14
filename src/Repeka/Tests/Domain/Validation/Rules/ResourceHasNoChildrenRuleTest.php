<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\AssertionFailedException;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\Validation\Rules\ResourceHasNoChildrenRule;

class ResourceHasNoChildrenRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var ResourceHasNoChildrenRule */
    private $rule;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->rule = new ResourceHasNoChildrenRule($this->resourceRepository);
    }

    public function testPositive() {
        $dummy = $this->createMock(ResourceEntity::class);
        $dummy->method('getId')->willReturn(0);
        $pageResult = new PageResult([], 1);
        $this->resourceRepository->expects($this->once())->method('findByQuery')->willReturn($pageResult);
        $result = $this->rule->validate($dummy);
        $this->assertTrue($result);
    }

    public function testNegative() {
        $dummy = $this->createMock(ResourceEntity::class);
        $dummy->method('getId')->willReturn(0);
        $pageResult = new PageResult([$dummy], 1);
        $this->resourceRepository->expects($this->once())->method('findByQuery')->willReturn($pageResult);
        $result = $this->rule->validate($dummy);
        $this->assertFalse($result);
    }

    public function testInvalidArgument() {
        $this->expectException(AssertionFailedException::class);
        $this->rule->validate(new \stdClass());
    }
}
