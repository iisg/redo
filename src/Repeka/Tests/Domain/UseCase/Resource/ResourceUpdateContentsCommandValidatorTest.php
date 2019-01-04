<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $this->resource = $this->createResourceMock(1, $resourceKind);
        $this->user = $this->createMock(User::class);
    }

    public function testValid() {
        $validator = new ResourceUpdateContentsCommandValidator();
        $command = new ResourceUpdateContentsCommand($this->resource, ResourceContents::empty(), $this->user);
        $validator->validate($command);
    }

    public function testInvalidForResourceIdLessThanZeroIfNotSystemResource() {
        $validator = new ResourceUpdateContentsCommandValidator();
        $command = new ResourceUpdateContentsCommand(
            $this->createResourceMock(-1024),
            ResourceContents::fromArray([1 => ['Some value']]),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }
}
