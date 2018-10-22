<?php
namespace Repeka\Plugins\MetadataValueRemover\Tests\Model;

use Repeka\Application\Service\PhpRegexNormalizer;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;
use Repeka\Plugins\MetadataValueRemover\Model\RepekaMetadataValueRemoverResourceWorkflowPlugin;
use Repeka\Tests\Traits\StubsTrait;

class RepekaMetadataValueRemoverResourceWorkflowPluginTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var RegexConstraint|\PHPUnit_Framework_MockObject_MockObject */
    private $regexConstraint;
    /** @var PhpRegexNormalizer|\PHPUnit_Framework_MockObject_MockObject */
    private $regexNormalizer;
    /** @var RepekaMetadataValueRemoverResourceWorkflowPlugin */
    private $valueRemover;

    /** @before */
    public function init() {
        $this->regexConstraint = $this->createMock(RegexConstraint::class);
        $this->regexNormalizer = $this->createMock(PhpRegexNormalizer::class);
        $this->valueRemover = new RepekaMetadataValueRemoverResourceWorkflowPlugin($this->regexConstraint, $this->regexNormalizer);
    }

    public function testDoesNothingIfNoConfig() {
        $this->assertEquals([], $this->getContentsAfterEvent([], [])->toArray());
    }

    private function getContentsAfterEvent(array $contents, array $config): ResourceContents {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('a')]);
        $resourceKind = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(1)], $workflow);
        $command = new ResourceTransitionCommand(
            $this->createResourceMock(1, $resourceKind),
            ResourceContents::fromArray($contents),
            $this->createWorkflowTransitionMock([], [], ['a'])
        );
        $event = new BeforeCommandHandlingEvent($command);
        $this->valueRemover->beforeEnterPlace(
            $event,
            new ResourceWorkflowPlacePluginConfiguration(['name' => 'repekaMetadataValueRemover', 'config' => $config])
        );
        return $event->getCommand()->getContents();
    }

    public function testDoesNothingForInvalidMetadataIds() {
        $this->assertEquals(
            [],
            $this->getContentsAfterEvent(
                [],
                ['metadataName' => 5, 'metadataValuePattern' => 'YY']
            )->toArray()
        );
    }

    public function testRemovesValue() {
        $metadataValuePattern = '/aa/';
        $this->regexConstraint->method('isConfigValid')->willReturn(true);
        $this->regexNormalizer->method('normalize')->willReturn($metadataValuePattern);
        $this->assertEquals(
            ResourceContents::fromArray([1 => []]),
            $this->getContentsAfterEvent([1 => 'aa'], ['metadataName' => 1, 'metadataValuePattern' => $metadataValuePattern])
        );
    }

    public function testDoesNotRemoveValue() {
        $metadataValuePattern = '/aa/';
        $this->regexConstraint->method('isConfigValid')->willReturn(true);
        $this->regexNormalizer->method('normalize')->willReturn($metadataValuePattern);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['bb']]),
            $this->getContentsAfterEvent([1 => 'bb'], ['metadataName' => 1, 'metadataValuePattern' => $metadataValuePattern])
        );
    }

    public function testRemovesSomeValues() {
        $metadataValuePattern = '/XX./';
        $this->regexConstraint->method('isConfigValid')->willReturn(true);
        $this->regexNormalizer->method('normalize')->willReturn($metadataValuePattern);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['aa', 'bb']]),
            $this->getContentsAfterEvent(
                [1 => ['XX1', 'aa', 'XX2', 'XX3', 'bb']],
                ['metadataName' => 1, 'metadataValuePattern' => $metadataValuePattern]
            )
        );
    }

    public function testDoesNotRemoveWhenEmpty() {
        $metadataValuePattern = '/XX/';
        $this->regexConstraint->method('isConfigValid')->willReturn(true);
        $this->regexNormalizer->method('normalize')->willReturn($metadataValuePattern);
        $this->assertEquals(
            ResourceContents::fromArray([1 => []]),
            $this->getContentsAfterEvent([], ['metadataName' => 1, 'metadataValuePattern' => $metadataValuePattern])
        );
    }

    public function testRemovesValueWithSubmetadata() {
        $metadataValuePattern = '/AA/';
        $this->regexConstraint->method('isConfigValid')->willReturn(true);
        $this->regexNormalizer->method('normalize')->willReturn($metadataValuePattern);
        $this->assertEquals(
            ResourceContents::fromArray([1 => ['BB']]),
            $this->getContentsAfterEvent(
                [1 => [['value' => 'AA', 'submetadata' => [2 => ['bb']]], ['value' => 'BB']]],
                ['metadataName' => 1, 'metadataValuePattern' => $metadataValuePattern]
            )
        );
    }
}
