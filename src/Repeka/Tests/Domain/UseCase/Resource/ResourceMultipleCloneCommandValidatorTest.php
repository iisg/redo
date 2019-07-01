<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceCloneManyTimesCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneManyTimesCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class ResourceMultipleCloneCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var User */
    private $executor;
    /** @var ResourceEntity */
    private $resource;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
        $this->resource = $this->createResourceMock(1);
    }

    public function testValid() {
        $validator = new ResourceCloneManyTimesCommandValidator();
        $command = new ResourceCloneManyTimesCommand($this->resource, 1, $this->executor);
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForCloneTimesOutOfRange() {
        $validator = new ResourceCloneManyTimesCommandValidator();
        $command = new ResourceCloneManyTimesCommand($this->resource, 51, $this->executor);
        $this->assertFalse($validator->isValid($command));
    }
}
