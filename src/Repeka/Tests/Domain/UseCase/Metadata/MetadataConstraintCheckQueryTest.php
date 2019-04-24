<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\UseCase\Metadata\MetadataConstraintCheckQuery;
use Repeka\Tests\Traits\StubsTrait;

class MetadataConstraintCheckQueryTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testCreatingWithResource() {
        $kind = $this->createResourceKindMock();
        $resource = $this->createResourceMock(1, $kind);
        $query = new MetadataConstraintCheckQuery('a', 'a', 'a', [], $resource);
        $this->assertEquals($resource, $query->getResource());
        $this->assertEquals($kind, $query->getResourceKind());
    }

    public function testCreatingWithResourceKind() {
        $kind = $this->createResourceKindMock();
        $query = new MetadataConstraintCheckQuery('a', 'a', 'a', [], $kind);
        $this->assertNull($query->getResource());
        $this->assertEquals($kind, $query->getResourceKind());
    }

    public function testCreatingWithNullSubject() {
        $this->expectException(\InvalidArgumentException::class);
        new MetadataConstraintCheckQuery('a', 'a', 'a', [], null);
    }

    public function testCreatingWithStringSubject() {
        $this->expectException(\InvalidArgumentException::class);
        new MetadataConstraintCheckQuery('a', 'a', 'a', [], 'aa');
    }

    public function testCreatingWithInvalidSubject() {
        $this->expectException(\InvalidArgumentException::class);
        new MetadataConstraintCheckQuery('a', 'a', 'a', [], new \DateTime());
    }
}
