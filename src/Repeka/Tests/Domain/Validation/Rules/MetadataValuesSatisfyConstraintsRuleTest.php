<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValuesSatisfyConstraintsRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var AbstractMetadataConstraint|\PHPUnit_Framework_MockObject_MockObject */
    private $constraint1;
    /** @var AbstractMetadataConstraint|\PHPUnit_Framework_MockObject_MockObject */
    private $constraint2;

    /** @var Metadata */
    private $metadataWithoutConstraints;
    /** @var Metadata */
    private $metadataWithConstraint1;
    /** @var Metadata */
    private $metadataWithBothConstraints;

    /** @var MetadataValuesSatisfyConstraintsRule */
    private $rule;

    /** @before */
    public function init() {
        $this->constraint1 = $this->createMock(AbstractMetadataConstraint::class);
        $this->constraint2 = $this->createMock(AbstractMetadataConstraint::class);
        $constraintManager = $this->createMetadataConstraintManagerStub(
            ['constraint1' => $this->constraint1, 'constraint2' => $this->constraint2]
        );
        $this->metadataWithoutConstraints = $this->createMetadataMock(1, null, MetadataControl::TEXT(), []);
        $this->metadataWithConstraint1 = $this->createMetadataMock(2, null, MetadataControl::TEXT(), ['constraint1' => 'c1m2cfg']);
        $this->metadataWithBothConstraints = $this->createMetadataMock(
            3,
            null,
            MetadataControl::TEXT(),
            [
                'constraint1' => 'c1m3cfg',
                'constraint2' => 'c2m3cfg',
            ]
        );
        $metadataRepository = $this->createRepositoryStub(
            MetadataRepository::class,
            [
                $this->metadataWithoutConstraints,
                $this->metadataWithConstraint1,
                $this->metadataWithBothConstraints,
            ]
        );
        $resourceKind = $this->createResourceKindMock(1, 'books', [$this->metadataWithConstraint1]);
        $this->rule = (new MetadataValuesSatisfyConstraintsRule($constraintManager, $metadataRepository))->forResourceKind($resourceKind);
    }

    public function testAcceptsWhenThereAreNoConstraints() {
        $this->assertTrue($this->rule->validate(ResourceContents::fromArray([$this->metadataWithoutConstraints->getId() => 1])));
    }

    public function testAcceptsWhenAllRulesAccept() {
        $this->constraint1->expects($this->exactly(2))->method('validateAll')
            ->withConsecutive(
                [$this->metadataWithConstraint1, 'c1m2cfg', ['a', 'b']],
                [$this->metadataWithBothConstraints, 'c1m3cfg', ['d']]
            );
        $this->constraint2->expects($this->once())->method('validateAll')->with($this->metadataWithBothConstraints, 'c2m3cfg', ['d']);
        $this->assertTrue(
            $this->rule->validate(
                ResourceContents::fromArray(
                    [
                        $this->metadataWithConstraint1->getId() => ['a', 'b'],
                        $this->metadataWithBothConstraints->getId() => 'd',
                    ]
                )
            )
        );
    }

    public function testRejectsWhenAnyRuleRejects() {
        $this->expectException(\InvalidArgumentException::class);
        $this->constraint2->method('validateAll')->willThrowException(new \InvalidArgumentException());
        $this->rule->validate(
            ResourceContents::fromArray(
                [
                    $this->metadataWithConstraint1->getId() => ['a', 'b'],
                    $this->metadataWithBothConstraints->getId() => 'd',
                ]
            )
        );
    }

    public function testValidatesSubmetadata() {
        $this->constraint1->expects($this->exactly(3))->method('validateAll')
            ->withConsecutive(
                [$this->metadataWithConstraint1, 'c1m2cfg', ['a', 'c']],
                [$this->metadataWithBothConstraints, 'c1m3cfg', ['b', 'c']],
                [$this->metadataWithBothConstraints, 'c1m3cfg', ['e']]
            );
        $this->constraint2->expects($this->exactly(2))->method('validateAll')
            ->withConsecutive(
                [$this->metadataWithBothConstraints, 'c2m3cfg', ['b', 'c']],
                [$this->metadataWithBothConstraints, 'c2m3cfg', ['e']]
            );
        $this->assertTrue(
            $this->rule->validate(
                ResourceContents::fromArray(
                    [
                        $this->metadataWithConstraint1->getId() => [
                            [
                                'value' => 'a',
                                'submetadata' => [
                                    $this->metadataWithBothConstraints->getId() => ['b', 'c'],
                                ],
                            ],
                            [
                                'value' => 'c',
                                'submetadata' => [
                                    $this->metadataWithBothConstraints->getId() => 'e',
                                ],
                            ],
                        ],
                    ]
                )
            )
        );
    }
}
