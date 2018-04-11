<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\AssertionFailedException;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $workflowRepository;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rule;

    protected function setUp() {
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->rule = new ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule(
            $this->workflowRepository,
            $this->createMock(ResourceKindRepository::class),
            $this->createMock(MetadataRepository::class)
        );
    }

    public function testFailsWithoutMetadataId() {
        $this->expectException(AssertionFailedException::class);
        $this->rule->validate([]);
    }

    public function testAcceptsNonRelationships() {
        $this->workflowRepository->expects($this->never())->method('findByAssigneeMetadata');
        $rule = $this->rule->forMetadata($this->createMetadataMock(1, 1, MetadataControl::TEXT()));
        $this->assertTrue($rule->validate([]));
    }

    public function testAcceptsCannotDeterminesAssignee() {
        $rule = $this->rule->forMetadata($this->createMetadataMock(1, 1, MetadataControl::RELATIONSHIP()));
        $this->assertTrue($rule->validate([]));
    }

    public function testAcceptsNonDependentWorkflows() {
        $metadata = $this->createMetadataMock();
        $metadata->method('canDetermineAssignees')->willReturn(true);
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with($metadata)->willReturn([]);
        $rule = $this->rule->forMetadata($metadata);
        $this->assertTrue($rule->validate([]));
    }

    public function testAcceptsIfStillCanDetermineAssignees() {
        $metadata = $this->createMetadataMock();
        $dummyWorkflow = $this->createMock(ResourceWorkflow::class);
        $metadata->method('canDetermineAssignees')->willReturn(true);
        $metadata->method('withOverrides')->with(['constraints' => ['a']])->willReturn($metadata);
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with($metadata)->willReturn([$dummyWorkflow]);
        $rule = $this->rule->forMetadata($metadata);
        $this->assertTrue($rule->validate(['a']));
    }

    public function testRejectsIfNewConstraintsCannotDetermineAssignees() {
        $metadata = $this->createMetadataMock();
        $dummyWorkflow = $this->createMock(ResourceWorkflow::class);
        $metadata->method('canDetermineAssignees')->willReturnOnConsecutiveCalls(true, false);
        $metadata->method('withOverrides')->with(['constraints' => ['a']])->willReturn($metadata);
        $this->workflowRepository->expects($this->once())->method('findByAssigneeMetadata')->with($metadata)->willReturn([$dummyWorkflow]);
        $rule = $this->rule->forMetadata($metadata);
        $this->assertFalse($rule->validate(['a']));
    }
}
