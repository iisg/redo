<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceMultipleCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceMultipleCloneCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceMultipleCloneCommandValidatorTest extends \PHPUnit_Framework_TestCase {
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
        $validator = new ResourceMultipleCloneCommandValidator();
        $command = new ResourceMultipleCloneCommand($this->resourceKind, $this->resource, ResourceContents::empty(), 1, $this->executor);
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForCloneTimesOutOfRange() {
        $validator = new ResourceMultipleCloneCommandValidator();
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            $this->resource,
            ResourceContents::fromArray([1 => ['Some value']]),
            51,
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = new ResourceMultipleCloneCommandValidator();
        $command = new ResourceMultipleCloneCommand(
            $this->createMock(ResourceKind::class),
            $this->resource,
            ResourceContents::fromArray([1 => ['Some value']]),
            1,
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidForGetResourceAsNumber() {
        $validator = new ResourceMultipleCloneCommandValidator();
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            1,
            ResourceContents::fromArray([1 => ['Some value']]),
            1,
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidForGetResourceAsNull() {
        $validator = new ResourceMultipleCloneCommandValidator();
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            null,
            ResourceContents::fromArray([1 => ['Some value']]),
            1,
            $this->executor
        );
        $this->assertFalse($validator->isValid($command));
    }
}
