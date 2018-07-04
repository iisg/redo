<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Integration;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class RepekaMetadataValueSetterPluginIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var RepekaMetadataValueSetterResourceWorkflowPlugin */
    private $listener;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->listener = $this->container->get(RepekaMetadataValueSetterResourceWorkflowPlugin::class);
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
                                [
                                    'name' => 'repekaMetadataValueSetter',
                                    'config' => [
                                        'metadataName' => 'Tytuł',
                                        'metadataValue' => 'PHP - to można leczyć!',
                                    ],
                                ],
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
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
        $resource = $this->getPhpBookResource();
        $newContent = $resource->getContents();
        $this->assertEquals($oldContent, $newContent);
    }

    public function testMetadataValueSetter() {
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
                                [
                                    'name' => 'repekaMetadataValueSetter',
                                    'config' => [
                                        'metadataName' => 'Opis',
                                        'metadataValue' => 'PHP - test',
                                    ],
                                ],
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
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
        $resource = $this->getPhpBookResource();
        $newContent = $resource->getContents();
        $this->assertNotEquals($oldContent, $newContent);
    }

    public function testMetadataValueSetterWithMultipleConfigs() {
        $resource = $this->getPhpBookResource();
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $workflow = $resource->getWorkflow();
        $places = array_map(
            function (ResourceWorkflowPlace $place) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => [
                                [
                                    'name' => 'repekaMetadataValueSetter',
                                    'config' => [
                                        'metadataName' => 'Tytuł',
                                        'metadataValue' => 'PHP - test',
                                    ],
                                ],
                                [
                                    'name' => 'repekaMetadataValueSetter',
                                    'config' => [
                                        'metadataName' => 'Opis',
                                        'metadataValue' => 'UNICORN {{ r|mTytuł }}',
                                    ],
                                ],
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
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand($resource, $resource->getContents(), $transitions[0]->getId(), $this->getAdminUser())
        );
        $resource = $this->getPhpBookResource();
        $newContent = $resource->getContents();
        $this->assertContains('PHP - test', $newContent->getValues($this->findMetadataByName('Tytuł')));
        $this->assertContains('UNICORN', $newContent->getValues($this->findMetadataByName('Opis'))[1]);
        $this->assertContains('PHP - test', $newContent->getValues($this->findMetadataByName('Opis'))[1]);
    }
}
