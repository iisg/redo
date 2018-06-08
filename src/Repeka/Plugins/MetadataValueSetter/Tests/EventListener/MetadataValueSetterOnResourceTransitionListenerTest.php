<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\EventListener;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Plugins\MetadataValueSetter\EventListener\MetadataValueSetterOnResourceTransitionListener;
use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValueSetterOnResourceTransitionListenerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var RepekaMetadataValueSetterResourceWorkflowPlugin|\PHPUnit_Framework_MockObject_MockObject */
    private $config;
    /** @var ResourceDisplayStrategyEvaluator|\PHPUnit_Framework_MockObject_MockObject */
    private $strategyEvaluator;
    /** @var MetadataValueSetterOnResourceTransitionListener */
    private $valueSetter;

    /** @before */
    public function init() {
        $this->config = $this->createMock(RepekaMetadataValueSetterResourceWorkflowPlugin::class);
        $this->strategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->valueSetter = new MetadataValueSetterOnResourceTransitionListener($this->config, $this->strategyEvaluator);
    }

    public function testDoNothing() {
        $this->assertEquals([], $this->getContentsAfterEvent([])->toArray());
    }

    public function testDoNothingForInvalidMetadataIds() {
        $this->config->method('getOptionFromPlaces')->willReturnOnConsecutiveCalls(['5'], ['YY']);
        $this->assertEquals([], $this->getContentsAfterEvent([])->toArray());
    }

    public function testInsertsValue() {
        $this->config->method('getOptionFromPlaces')->willReturnOnConsecutiveCalls(['1'], ['YY']);
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(ResourceContents::fromArray([1 => 'YY']), $this->getContentsAfterEvent([]));
    }

    public function testAddsValue() {
        $this->config->method('getOptionFromPlaces')->willReturnOnConsecutiveCalls(['1'], ['YY']);
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(ResourceContents::fromArray([1 => ['XX', 'YY']]), $this->getContentsAfterEvent([1 => 'XX']));
    }

    public function testGeneratesValueBasedOnNewContent() {
        $this->config->method('getOptionFromPlaces')->willReturnOnConsecutiveCalls(['1'], ['YY']);
        $this->strategyEvaluator->method('render')->willReturnCallback(
            function (ResourceContents $contents) {
                return $contents->getValues(2)[0];
            }
        );
        $this->assertEquals(ResourceContents::fromArray([1 => 'AA', 2 => 'AA']), $this->getContentsAfterEvent([2 => 'AA']));
    }

    private function getContentsAfterEvent(array $contents): ResourceContents {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('a')]);
        $resourceKind = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(1)], $workflow);
        $command = new ResourceTransitionCommand(
            $this->createResourceMock(1, $resourceKind),
            ResourceContents::fromArray($contents),
            $this->createWorkflowTransitionMock([], [], ['a'])
        );
        $event = new BeforeCommandHandlingEvent($command);
        $this->valueSetter->onResourceTransition($event);
        return $event->getCommand()->getContents();
    }
}
