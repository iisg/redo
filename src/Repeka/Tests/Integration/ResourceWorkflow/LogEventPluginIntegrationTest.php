<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Integration;

use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Tests\Integration\ResourceWorkflow\ResourceWorkflowPluginIntegrationTest;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class LogEventPluginIntegrationTest extends ResourceWorkflowPluginIntegrationTest {
    use FixtureHelpers;

    /** @var EventLogRepository */
    private $eventLogRepository;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->eventLogRepository = $this->container->get(EventLogRepository::class);
    }

    public function testSavingEventLog() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'logEvent',
                    'config' => [
                        'eventName' => 'abc',
                        'eventGroup' => 'def',
                        'logOnEdit' => true,
                    ],
                ],
            ]
        );
        $this->assertCount(1, $this->eventLogRepository->findAll());
        $entry = $this->eventLogRepository->findAll()[0];
        $this->assertEquals('abc', $entry->getEventName());
        $this->assertEquals('def', $entry->getEventGroup());
        $this->assertEquals($resource->getId(), $entry->getResource()->getId());
    }

    public function testSavingEventLogWithoutGroup() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'logEvent',
                    'config' => [
                        'eventName' => 'abc',
                        'eventGroup' => '',
                        'logOnEdit' => true,
                    ],
                ],
            ]
        );
        $this->assertCount(1, $this->eventLogRepository->findAll());
        $entry = $this->eventLogRepository->findAll()[0];
        $this->assertEquals('abc', $entry->getEventName());
        $this->assertEquals('default', $entry->getEventGroup());
        $this->assertEquals($resource->getId(), $entry->getResource()->getId());
    }

    public function testNotSavingEventLogWhenNoLogsOnEditGroup() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'logEvent',
                    'config' => [
                        'eventName' => 'abc',
                        'eventGroup' => '',
                        'logOnEdit' => false,
                    ],
                ],
            ]
        );
        $this->assertEmpty($this->eventLogRepository->findAll());
    }
}
