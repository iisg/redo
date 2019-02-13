<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQueryValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceListFtsQueryValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceListFtsQueryValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new ResourceListFtsQueryValidator();
    }

    public function testAcceptsPhraseOnlyQuery() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->build();
        $this->assertTrue($this->validator->isValid($query));
    }

    public function testAcceptsWhitelistedFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[$this->createMetadataMock(), [123]]])
            ->build();
        $this->assertTrue($this->validator->isValid($query));
    }

    public function testAcceptsWhitelistedFacetFiltersWithValueGivenAsString() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[$this->createMetadataMock(), ['123']]])
            ->build();
        $this->assertTrue($this->validator->isValid($query));
    }

    public function testAcceptsKindIdFilterIfFacetedByResourceKind() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setResourceKindFacet()
            ->setFacetsFilters([['kindId', ['123']]])
            ->build();
        $this->assertTrue($this->validator->isValid($query));
    }

    public function testRejectsWhenFacetFilterIsNotAnArray() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[1, 123]])
            ->build();
        $this->assertFalse($this->validator->isValid($query));
    }

    public function testRejectsWhenFacetFilterValueIsNotMetadata() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[1, ['unicorn']]])
            ->build();
        $this->assertFalse($this->validator->isValid($query));
    }

    public function testRejectsNotWhitelistedFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[$this->createMetadataMock(2), [123]]])
            ->build();
        $this->assertFalse($this->validator->isValid($query));
    }

    public function testTellsWhichFacetIsForbidden() {
        $this->expectExceptionMessage('123');
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets([$this->createMetadataMock(1)])
            ->setFacetsFilters([[$this->createMetadataMock(123), [123]]])
            ->build();
        $this->validator->validate($query);
    }
}
