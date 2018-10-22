<?php
namespace Repeka\Plugins\MetadataValueRemover\Tests\Integration;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Plugins\MetadataValueRemover\Model\RepekaMetadataValueRemoverResourceWorkflowPlugin;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class RepekaMetadataValueRemoverPluginIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var RepekaMetadataValueRemoverResourceWorkflowPlugin */
    private $listener;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->listener = $this->container->get(RepekaMetadataValueRemoverResourceWorkflowPlugin::class);
    }

    public function testMetadataValueRemover() {
        $resource = $this->getPhpBookResource();
        $oldContent = $resource->getContents();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueRemover',
                    'config' => [
                        'metadataName' => 'opis',
                        'metadataValuePattern' => 'Poradnik.*',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotEquals($oldContent, $newContent);
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

    public function testMetadataValueRemoverWithMultipleConfigs() {
        $metadataName1 = 'liczba_stron';
        $metadataName2 = 'Opis';
        $resource = $this->getPhpBookResource();
        $content = $resource->getContents();
        $this->assertContains(1337, $content->getValuesWithoutSubmetadata($this->findMetadataByName($metadataName1)));
        $this->assertContains(
            'Poradnik dla cierpiących na zwyrodnienie interpretera.',
            $content->getValuesWithoutSubmetadata($this->findMetadataByName($metadataName2))
        );
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueRemover',
                    'config' => [
                        'metadataName' => $metadataName1,
                        'metadataValuePattern' => '13.*',
                    ],
                ],
                [
                    'name' => 'repekaMetadataValueRemover',
                    'config' => [
                        'metadataName' => $metadataName2,
                        'metadataValuePattern' => 'Poradnik.*',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotContains(1337, $newContent->getValuesWithoutSubmetadata($this->findMetadataByName($metadataName1)));
        $this->assertNotContains(
            'Poradnik dla cierpiących na zwyrodnienie interpretera.',
            $newContent->getValuesWithoutSubmetadata($this->findMetadataByName($metadataName2))
        );
    }
}
