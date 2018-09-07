<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandAdjuster;
use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;
use Repeka\Tests\Traits\StubsTrait;

class RepekaMetadataValueSetterResourceWorkflowPluginTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceDisplayStrategyEvaluator|\PHPUnit_Framework_MockObject_MockObject */
    private $strategyEvaluator;
    /** @var RepekaMetadataValueSetterResourceWorkflowPlugin */
    private $valueSetter;
    private $resourceTransitionCommandAdjuster;

    /** @before */
    public function init() {
        $this->resourceTransitionCommandAdjuster = $this->createMock(ResourceTransitionCommandAdjuster::class);
        $this->strategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class);
        $this->valueSetter = new RepekaMetadataValueSetterResourceWorkflowPlugin(
            $this->resourceTransitionCommandAdjuster,
            $this->strategyEvaluator,
            $metadataRepository
        );
    }

    public function testDoesNothingIfNoConfig() {
        $this->assertEquals([], $this->getContentsAfterEvent([], [], [])->toArray());
    }

    public function testDoesNothingForInvalidMetadataIds() {
        $this->assertEquals(
            [],
            $this->getContentsAfterEvent(
                [],
                [],
                ['metadataName' => 5, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => false]
            )->toArray()
        );
    }

    public function testInsertsValue() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            ResourceContents::fromArray([1 => 'YY']),
            $this->getContentsAfterEvent([], [1 => 'YY'], ['metadataName' => 1, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => false])
        );
    }

    public function testAddsValue() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['XX', 'YY']]),
            $this->getContentsAfterEvent(
                [1 => 'XX'],
                [1 => ['XX', 'YY']],
                ['metadataName' => 1, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => false]
            )
        );
    }

    public function testDoesNothingWhenNotEmptyAndConfigSet() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['XX']]),
            $this->getContentsAfterEvent(
                [1 => 'XX'],
                [1 => ['XX']],
                ['metadataName' => 1, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => true]
            )
        );
    }

    public function testInsertsValueWhenEmptyAndConfigSet() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['XX']]),
            $this->getContentsAfterEvent([], [1 => ['XX']], ['metadataName' => 1, 'metadataValue' => 'XX', 'setOnlyWhenEmpty' => true])
        );
    }

    public function testDoNothingWhenDuplicatedValue() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['XX']]),
            $this->getContentsAfterEvent(
                [1 => 'XX'],
                [1 => ['XX']],
                ['metadataName' => 1, 'metadataValue' => 'XX', 'setOnlyWhenEmpty' => false]
            )
        );
    }

    public function testGeneratesValueBasedOnNewContent() {
        $this->strategyEvaluator->method('render')->willReturnCallback(
            function (ResourceContents $contents) {
                return $contents->getValuesWithoutSubmetadata(2)[0];
            }
        );
        $this->assertEquals(
            ResourceContents::fromArray([1 => 'AA', 2 => 'AA']),
            $this->getContentsAfterEvent(
                [2 => 'AA'],
                [1 => 'AA', 2 => 'AA'],
                ['metadataName' => 1, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => false]
            )
        );
    }

    public function testOptionFalseSameAsNotDefined() {
        $this->strategyEvaluator->method('render')->willReturnArgument(1);
        $this->assertEquals(
            $this->getContentsAfterEvent(
                [1 => 'XX'],
                [1 => 'XX'],
                ['metadataName' => 1, 'metadataValue' => 'YY', 'setOnlyWhenEmpty' => false]
            ),
            $this->getContentsAfterEvent([1 => 'XX'], [1 => 'XX'], ['metadataName' => 1, 'metadataValue' => 'YY',])
        );
    }

    private function getContentsAfterEvent(array $contents, array $expectedContents, array $config): ResourceContents {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('a')]);
        $resourceKind = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(1)], $workflow);
        $command = new ResourceTransitionCommand(
            $this->createResourceMock(1, $resourceKind),
            ResourceContents::fromArray($contents),
            $this->createWorkflowTransitionMock([], [], ['a'])
        );
        $expectedCommand = new ResourceTransitionCommand(
            $this->createResourceMock(1, $resourceKind),
            ResourceContents::fromArray($expectedContents),
            $this->createWorkflowTransitionMock([], [], ['a'])
        );
        $this->resourceTransitionCommandAdjuster->method('adjustCommand')->willReturn($expectedCommand);
        $event = new BeforeCommandHandlingEvent($command);
        $this->valueSetter->beforeEnterPlace(
            $event,
            new ResourceWorkflowPlacePluginConfiguration(['name' => 'repekaMetadataValueSetter', 'config' => $config])
        );
        return $event->getCommand()->getContents();
    }
}
