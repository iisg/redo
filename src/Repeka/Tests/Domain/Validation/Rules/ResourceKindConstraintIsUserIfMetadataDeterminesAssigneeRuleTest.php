<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\AssertionFailedException;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;

class ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflowRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $workflowRepository;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rule;

    protected function setUp() {
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->rule = new ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule($this->workflowRepository);
    }

    public function testFailsWithoutMetadataId() {
        $this->expectException(AssertionFailedException::class);
        $this->rule->validate([]);
    }

    public function testAcceptsNonRelationships() {
        $this->workflowRepository->expects($this->never())->method('findByAssigneeMetadata');
        $result = $this->rule->forMetadataId(123);
        $this->assertTrue($result->validate([]));
    }

    public function testAcceptsNonDependencies() {
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with(123)->willReturn([]);
        $result = $this->rule->forMetadataId(123)->validate(['resourceKind' => [SystemResourceKind::USER + 1]]);
        $this->assertTrue($result);
    }

    public function testAcceptsUserRelationshipDependencies() {
        $dummyWorkflow = $this->createMock(ResourceWorkflow::class);
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with(123)->willReturn([$dummyWorkflow]);
        $result = $this->rule->forMetadataId(123)->validate(['resourceKind' => [SystemResourceKind::USER]]);
        $this->assertTrue($result);
    }

    public function testRejectsOtherRelationshipDependencies() {
        $dummyWorkflow = $this->createMock(ResourceWorkflow::class);
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with(123)->willReturn([$dummyWorkflow]);
        $result = $this->rule->forMetadataId(123)->validate(['resourceKind' => [SystemResourceKind::USER + 1]]);
        $this->assertFalse($result);
    }
}
