<?php
namespace Repeka\Tests\Application\Validation;

use Repeka\Application\Validation\ContainerAwareMetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintManagerTest extends \PHPUnit_Framework_TestCase {
    /** @var ContainerAwareMetadataConstraintManager */
    private $provider;

    public function testReturnsRegisteredService() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->once())->method('getConstraintName')->willReturn('test');
        $this->provider = new ContainerAwareMetadataConstraintManager([$constraint]);
        $this->assertEquals($constraint, $this->provider->get('test'));
    }

    public function testThrowsOnUnknownService() {
        $this->expectException(\Exception::class);
        $dummy = $this->createMock(AbstractMetadataConstraint::class);
        $this->provider = new ContainerAwareMetadataConstraintManager([$dummy]);
        $this->provider->get('nonexistent');
    }

    public function testTracksRequiredConstraints() {
        $constraint1 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint1->method('getConstraintName')->willReturn('test1');
        $constraint1->method('getSupportedControls')->willReturn(['text']);
        $this->provider = new ContainerAwareMetadataConstraintManager([$constraint1]);
        $constraint2 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint2->method('getConstraintName')->willReturn('test2');
        $constraint2->method('getSupportedControls')->willReturn(['integer']);
        $this->provider = new ContainerAwareMetadataConstraintManager([$constraint1, $constraint2]);
        $constraint3 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint3->method('getConstraintName')->willReturn('test3');
        $constraint3->method('getSupportedControls')->willReturn(['text']);
        $this->provider = new ContainerAwareMetadataConstraintManager([$constraint1, $constraint2, $constraint3]);
        $this->assertEquals(['test1', 'test3'], $this->provider->getSupportedConstraintNamesForControl('text'));
        $this->assertEquals(['test2'], $this->provider->getSupportedConstraintNamesForControl('integer'));
    }

    public function testTracksMandatoryConstraints() {
        $constraintMandatory = $this->createMock(AbstractMetadataConstraint::class);
        $constraintMandatory->method('getConstraintName')->willReturn('mandatory');
        $constraintMandatory->method('isMandatory')->willReturn(true);
        $constraintMandatory->method('getSupportedControls')->willReturn(['integer']);
        $this->provider = new ContainerAwareMetadataConstraintManager([$constraintMandatory]);
        $actualMandatoryConstraints = $this->provider->getMandatoryConstraintsForControl('integer');
        $this->assertCount(1, $actualMandatoryConstraints);
        $this->assertSame($constraintMandatory, $actualMandatoryConstraints[0]);
        $this->assertEquals(['mandatory'], $this->provider->getSupportedConstraintNamesForControl('integer'));
    }

    public function testReturnsEmptyArrayByDefault() {
        $this->provider = new ContainerAwareMetadataConstraintManager([]);
        $this->assertEquals([], $this->provider->getSupportedConstraintNamesForControl('relationship'));
    }
}
