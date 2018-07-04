<?php
namespace Repeka\Plugins\Ocr\Tests\Integration;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Plugins\Ocr\Model\OcrCommunicator;
use Repeka\Plugins\Ocr\Model\OcrOnResourceTransitionListener;
use Repeka\Plugins\Ocr\Model\RepekaOcrResourceWorkflowPlugin;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class RepekaOcrPluginIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var OcrCommunicator|\PHPUnit_Framework_MockObject_MockObject */
    private $communicator;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $plugin = $this->container->get(RepekaOcrResourceWorkflowPlugin::class);
        $this->communicator = $this->createMock(OcrCommunicator::class);
        $plugin->setCommunicator($this->communicator);
    }

    public function testDoesNotOcrIfNotConfiguredEvent() {
        $resource = $this->getPhpBookResource();
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $this->communicator->expects($this->never())->method('sendToOcr');
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
    }

    public function testSendsToOcrIfSubscribed() {
        $resource = $this->getPhpBookResource();
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $titleMetadata = $this->findMetadataByName('Tytuł');
        $titles = $resource->getValues($titleMetadata);
        $this->communicator->expects($this->once())->method('sendToOcr')->with($titles);
        $workflow = $resource->getWorkflow();
        $places = array_map(
            function (ResourceWorkflowPlace $place) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => [
                                ['name' => 'repekaOcr', 'config' => ['metadataToOcr' => 'Tytuł']],
                            ],
                        ]
                    )
                );
            },
            $workflow->getPlaces()
        );
        $this->handleCommandBypassingFirewall(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $places,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
        $resource = $this->getPhpBookResource();
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
    }
}
