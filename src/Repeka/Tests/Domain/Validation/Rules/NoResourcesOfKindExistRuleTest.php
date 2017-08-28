<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\Rules\NoResourcesOfKindExistRule;

class NoResourcesOfKindExistRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var  ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var NoResourcesOfKindExistRule */
    private $rule;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->rule = new NoResourcesOfKindExistRule($this->resourceRepository);
    }

    public function testPositiveWhenNoResourcesOfKind() {
        $this->resourceRepository->expects($this->once())->method('countByResourceKind')->willReturn(0);
        $this->assertTrue($this->rule->validate($this->createMock(ResourceKind::class)));
    }

    public function testNegativeWhenSomeResourcesUseKind() {
        $this->resourceRepository->expects($this->once())->method('countByResourceKind')->willReturn(1);
        $this->assertFalse($this->rule->validate($this->createMock(ResourceKind::class)));
    }
}
