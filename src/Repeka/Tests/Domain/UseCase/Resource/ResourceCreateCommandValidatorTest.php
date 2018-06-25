<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ReproductorIsValidRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var User */
    private $executor;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
    }

    public function testValid() {
        $validator = new ResourceCreateCommandValidator();
        $resourceKind = $this->createResourceKindMock();
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::empty(), $this->executor);
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = new ResourceCreateCommandValidator();
        $command = new ResourceCreateCommand(
            $this->createMock(ResourceKind::class),
            ResourceContents::fromArray([1 => ['Some value']]),
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testValidForCreatingTopLevelResource() {
        $validator = new ResourceCreateCommandValidator();
        $resourceKind = $this->createResourceKindMock();
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::fromArray([1 => ['Some value']]), $this->executor);
        $this->assertTrue($validator->isValid($command));
    }

    public function testValidForCreatingResourceWithParent() {
        $resourceKind = $this->createResourceKindMock();
        $validator = new ResourceCreateCommandValidator();
        $command = new ResourceCreateCommand(
            $resourceKind,
            ResourceContents::fromArray([1 => ['Value'], SystemMetadata::PARENT => [1]]),
            $this->executor
        );
        $this->assertTrue($validator->isValid($command));
    }
}
