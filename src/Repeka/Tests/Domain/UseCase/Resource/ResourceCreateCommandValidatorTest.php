<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testValid() {
        $validator = new ResourceCreateCommandValidator();
        $resourceKind = $this->createResourceKindMock();
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::empty());
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = new ResourceCreateCommandValidator();
        $command = new ResourceCreateCommand($this->createMock(ResourceKind::class), ResourceContents::fromArray([1 => ['Some value']]));
        $this->assertFalse($validator->isValid($command));
    }
}
