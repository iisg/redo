<?php
namespace Repeka\Tests\Application\Validation;

use Repeka\Application\Validation\ContainerAwareMetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintManagerTest extends \PHPUnit_Framework_TestCase {
    /** @var ContainerAwareMetadataConstraintManager */
    private $provider;

    protected function setUp() {
        $this->provider = new ContainerAwareMetadataConstraintManager();
    }

    public function testReturnsRegisteredService() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->once())->method('getConstraintName')->willReturn('test');
        $this->provider->register($constraint);
        $this->assertEquals($constraint, $this->provider->get('test'));
    }

    public function testThrowsOnUnknownService() {
        $this->expectException(\Exception::class);
        $dummy = $this->createMock(AbstractMetadataConstraint::class);
        $this->provider->register($dummy);
        $this->provider->get('nonexistent');
    }

    public function testTracksRequiredConstraints() {
        $constraint1 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint1->method('getConstraintName')->willReturn('test1');
        $constraint1->expects($this->once())->method('getSupportedControls')->willReturn(['text']);
        $this->provider->register($constraint1);
        $constraint2 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint2->method('getConstraintName')->willReturn('test2');
        $constraint2->expects($this->once())->method('getSupportedControls')->willReturn(['integer']);
        $this->provider->register($constraint2);
        $constraint3 = $this->createMock(AbstractMetadataConstraint::class);
        $constraint3->method('getConstraintName')->willReturn('test3');
        $constraint3->expects($this->once())->method('getSupportedControls')->willReturn(['text']);
        $this->provider->register($constraint3);
        $this->assertEquals(['test1', 'test3'], $this->provider->getSupportedConstraintNamesForControl('text'));
        $this->assertEquals(['test2'], $this->provider->getSupportedConstraintNamesForControl('integer'));
    }

    public function testReturnsEmptyArrayByDefault() {
        $this->assertEquals([], $this->provider->getSupportedConstraintNamesForControl('relationship'));
    }
}
