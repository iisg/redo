<?php
namespace Repeka\Plugins\MetadataValueRemover\Tests\Integration;

use Repeka\Plugins\MetadataValueRemover\Model\RepekaMetadataValueRemoverResourceWorkflowPlugin;
use Repeka\Tests\Integration\ResourceWorkflow\ResourceWorkflowPluginIntegrationTest;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class RepekaMetadataValueRemoverPluginIntegrationTest extends ResourceWorkflowPluginIntegrationTest {
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

    public function testRemovingRelatedBook() {
        $resource = $this->getPhpBookResource();
        $oldContent = $resource->getContents();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueRemover',
                    'config' => [
                        'metadataName' => 'powiazanaKsiazka',
                        'metadataValuePattern' => '1.',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotEquals($oldContent, $newContent);
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
