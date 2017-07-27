<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class MetadataListByResourceClassQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var MetadataListByResourceClassQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new MetadataListByResourceClassQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new MetadataListByResourceClassQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = new MetadataListByResourceClassQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = new MetadataListByResourceClassQuery('');
        $this->assertFalse($this->validator->isValid($command));
    }
}
