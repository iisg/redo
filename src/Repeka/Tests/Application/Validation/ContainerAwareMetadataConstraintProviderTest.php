<?php
namespace Repeka\Tests\Application\Validation;

use Repeka\Application\Validation\ContainerAwareMetadataConstraintProvider;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintProviderTest extends \PHPUnit_Framework_TestCase {
    /** @var ContainerAwareMetadataConstraintProvider */
    private $provider;

    protected function setUp() {
        $this->provider = new ContainerAwareMetadataConstraintProvider();
    }

    public function testReturnsRegisteredService() {
        $mock = $this->createMock(AbstractMetadataConstraint::class);
        $mock->expects($this->once())->method('getConstraintName')->willReturn('test');
        $this->provider->register($mock);
        $this->assertEquals($mock, $this->provider->get('test'));
    }

    public function testThrowsOnUnknownService() {
        $this->expectException(\Exception::class);
        $dummy = $this->createMock(AbstractMetadataConstraint::class);
        $this->provider->register($dummy);
        $this->provider->get('nonexistent');
    }
}
