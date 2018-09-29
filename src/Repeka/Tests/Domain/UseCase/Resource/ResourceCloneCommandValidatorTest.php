<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCloneCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var User */
    private $executor;
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceKind */
    private $resourceKind;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
        $this->resource = $this->createResourceMock(1);
        $this->resourceKind = $this->createResourceKindMock();
    }

    public function testValid() {
        $validator = new ResourceCloneCommandValidator();
        $command = new ResourceCloneCommand($this->resourceKind, $this->resource, ResourceContents::empty(), $this->executor);
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = new ResourceCloneCommandValidator();
        $command = new ResourceCloneCommand(
            $this->createMock(ResourceKind::class),
            $this->resource,
            ResourceContents::fromArray([1 => ['Some value']]),
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidForGetResourceAsNumber() {
        $validator = new ResourceCloneCommandValidator();
        $command = new ResourceCloneCommand($this->resourceKind, 1, ResourceContents::fromArray([1 => ['Some value']]), $this->executor);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidForGetResourceAsNull() {
        $validator = new ResourceCloneCommandValidator();
        $command = new ResourceCloneCommand($this->resourceKind, null, ResourceContents::fromArray([1 => ['Some value']]), $this->executor);
        $this->assertFalse($validator->isValid($command));
    }
}
