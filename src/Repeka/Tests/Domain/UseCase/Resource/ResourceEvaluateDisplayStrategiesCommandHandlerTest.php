<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceEvaluateDisplayStrategiesCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEvaluateDisplayStrategiesCommandHandler */
    private $handler;
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    private $evaluator;
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $displayStrategyMetadata;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->evaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->evaluator->method('render')->with($this->anything(), 'AAA')->willReturn('BBB');
        $this->handler = new ResourceEvaluateDisplayStrategiesCommandHandler($this->evaluator, $this->resourceRepository);
        $this->displayStrategyMetadata = $this->createMetadataMock(
            10,
            null,
            MetadataControl::DISPLAY_STRATEGY(),
            ['displayStrategy' => 'AAA']
        );
    }

    public function testDoesNothingIfNoDynamicMetadata() {
        $resource = $this->createResourceMock(
            1,
            $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(1, null, MetadataControl::TEXT())]),
            [1 => 'TEST']
        );
        $this->resourceRepository->expects($this->never())->method('save');
        $command = new ResourceEvaluateDisplayStrategiesCommand($resource);
        $evaluated = $this->handler->handle($command);
        $this->assertSame($resource, $evaluated);
    }

    public function testEvaluatesNewValueForDynamicMetadata() {
        $resource = $this->createResourceMock(
            1,
            $this->createResourceKindMock(
                1,
                'books',
                [$this->createMetadataMock(1, null, MetadataControl::TEXT()), $this->displayStrategyMetadata]
            ),
            [1 => 'TEST']
        );
        $this->resourceRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $command = new ResourceEvaluateDisplayStrategiesCommand($resource);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function (ResourceContents $contents) {
                $this->assertEquals(['BBB'], $contents->getValuesWithoutSubmetadata($this->displayStrategyMetadata));
            }
        );
        $evaluated = $this->handler->handle($command);
        $this->assertSame($resource, $evaluated);
    }

    public function testEvaluatesChangedValueForDynamicMetadata() {
        $resource = $this->createResourceMock(
            1,
            $this->createResourceKindMock(
                1,
                'books',
                [$this->createMetadataMock(1, null, MetadataControl::TEXT()), $this->displayStrategyMetadata]
            ),
            [1 => 'TEST', 10 => 'Other Value']
        );
        $this->resourceRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $command = new ResourceEvaluateDisplayStrategiesCommand($resource);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function (ResourceContents $contents) {
                $this->assertEquals(['BBB'], $contents->getValuesWithoutSubmetadata($this->displayStrategyMetadata));
            }
        );
        $evaluated = $this->handler->handle($command);
        $this->assertSame($resource, $evaluated);
    }

    public function testDoesNotSaveChangesIfEvaluatedTheSame() {
        $resource = $this->createResourceMock(
            1,
            $this->createResourceKindMock(
                1,
                'books',
                [$this->createMetadataMock(1, null, MetadataControl::TEXT()), $this->displayStrategyMetadata]
            ),
            [1 => 'TEST', 10 => 'BBB']
        );
        $this->resourceRepository->expects($this->never())->method('save')->willReturnArgument(0);
        $command = new ResourceEvaluateDisplayStrategiesCommand($resource);
        $evaluated = $this->handler->handle($command);
        $this->assertSame($resource, $evaluated);
    }
}
