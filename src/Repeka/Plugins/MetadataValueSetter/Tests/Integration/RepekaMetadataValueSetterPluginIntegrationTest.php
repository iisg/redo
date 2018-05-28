<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Integration;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Plugins\MetadataValueSetter\EventListener\MetadataValueSetterOnResourceTransitionListener;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Repeka\Tests\TestContainerPass;

class RepekaMetadataValueSetterPluginIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var MetadataValueSetterOnResourceTransitionListener */
    private $listener;

    public function prepareIntegrationTest() {
        TestContainerPass::addPublicServices([MetadataValueSetterOnResourceTransitionListener::class]);
        parent::prepareIntegrationTest();
    }

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->listener = $this->container->get(MetadataValueSetterOnResourceTransitionListener::class);
    }

    public function testDoesNotSetMetadataIfDuplicatedValues() {
        $resource = $this->getPhpBookResource();
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $workflow = $resource->getWorkflow();
        $oldContent = $resource->getContents();
        $places = array_map(
            function (ResourceWorkflowPlace $place) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => [
                                'repekaMetadataValueSetter' => [
                                    'metadataName' => 'Tytuł',
                                    'metadataValue' => 'PHP - to można leczyć!',
                                ],
                            ],
                        ]
                    )
                );
            },
            $workflow->getPlaces()
        );
        $this->handleCommand(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $places,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
        $this->handleCommand(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
        $resource = $this->getPhpBookResource();
        $newContent = $resource->getContents();
        $this->assertEquals($oldContent, $newContent);
    }

    public function testMetdatataValueSetter() {
        $resource = $this->getPhpBookResource();
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $workflow = $resource->getWorkflow();
        $oldContent = $resource->getContents();
        $places = array_map(
            function (ResourceWorkflowPlace $place) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => [
                                'repekaMetadataValueSetter' => [
                                    'metadataName' => 'Opis',
                                    'metadataValue' => 'PHP - test',
                                ],
                            ],
                        ]
                    )
                );
            },
            $workflow->getPlaces()
        );
        $this->handleCommand(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $places,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
        $this->handleCommand(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
        $resource = $this->getPhpBookResource();
        $newContent = $resource->getContents();
        $this->assertNotEquals($oldContent, $newContent);
    }
}
