<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Integration;

use Repeka\Domain\Entity\ResourceEntity;
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

    /**
     * @param $resource ResourceEntity
     * @param $pluginConfiguration
     */
    protected function usePluginWithResource($resource, $pluginConfiguration) {
        $transitions = $resource->getWorkflow()->getTransitions($resource);
        $workflow = $resource->getWorkflow();
        $places = array_map(
            function (ResourceWorkflowPlace $place) use ($pluginConfiguration) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => $pluginConfiguration,
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
    }

    public function testMetadataValueSetter() {
        $resource = $this->getPhpBookResource();
        $oldContent = $resource->getContents();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'opis ',
                        'metadataValue' => 'PHP - test',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotEquals($oldContent, $newContent);
    }

    public function testMetadataValueSetterWithMultipleConfigs() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'Tytuł',
                        'metadataValue' => 'PHP - test',
                        'setOnlyWhenEmpty' => false,
                    ],
                ],
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'Opis',
                        'metadataValue' => 'UNICORN {{ r|mTytuł }}',
                        'setOnlyWhenEmpty' => false,
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertContains('PHP - test', $newContent->getValuesWithoutSubmetadata($this->findMetadataByName('Tytuł')));
        $this->assertContains('UNICORN', $newContent->getValuesWithoutSubmetadata($this->findMetadataByName('Opis'))[1]);
        $this->assertContains('PHP - test', $newContent->getValuesWithoutSubmetadata($this->findMetadataByName('Opis'))[1]);
    }
}
