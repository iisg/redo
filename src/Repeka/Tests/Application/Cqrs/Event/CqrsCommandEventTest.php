<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Application\Cqrs\Event\CqrsCommandEvent;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;

class CqrsCommandEventTest extends \PHPUnit_Framework_TestCase {
    public function testGeneratingName() {
        $name = CqrsCommandEvent::getEventNameFromClasses(
            BeforeCommandHandlingEvent::class,
            ResourceCreateCommand::class
        );
        $this->assertEquals('command_before.resource_create', $name);
    }
}
