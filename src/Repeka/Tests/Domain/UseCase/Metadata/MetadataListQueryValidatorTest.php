<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class MetadataListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var MetadataListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new MetadataListQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = MetadataListQuery::builder()->filterByResourceClass('books')->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = MetadataListQuery::builder()->filterByResourceClass('invalidRC')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = MetadataListQuery::builder()->filterByResourceClass('')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidWhenNullResourceClass() {
        $command = MetadataListQuery::builder()->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassValidControl() {
        $command = MetadataListQuery::builder()->filterByControl(MetadataControl::TEXTAREA())->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWithInvalidControl() {
        $command = MetadataListQuery::builder()->filterByControls(['text'])->build();
        $this->assertFalse($this->validator->isValid($command));
    }
}
