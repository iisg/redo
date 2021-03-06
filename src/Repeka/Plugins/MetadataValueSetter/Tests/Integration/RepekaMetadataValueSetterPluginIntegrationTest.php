<?php
namespace Repeka\Plugins\MetadataValueSetter\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;
use Repeka\Tests\Integration\ResourceWorkflow\ResourceWorkflowPluginIntegrationTest;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class RepekaMetadataValueSetterPluginIntegrationTest extends ResourceWorkflowPluginIntegrationTest {
    use FixtureHelpers;

    /** @var RepekaMetadataValueSetterResourceWorkflowPlugin */
    private $listener;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->listener = $this->container->get(RepekaMetadataValueSetterResourceWorkflowPlugin::class);
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

    public function testSettingParentAsInteger() {
        $resource = $this->getPhpBookResource();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'parent',
                        'metadataValue' => '1',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertSame([1], $newContent->getValuesWithoutSubmetadata(SystemMetadata::PARENT));
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

    public function testSettingTheValueConditionally() {
        $resource = $this->getPhpBookResource();
        $oldContent = $resource->getContents();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'opis',
                        'metadataValue' => '{% if 1==2 %}UNICORN{%endif%}',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertEquals($oldContent, $newContent);
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'opis',
                        'metadataValue' => '{%if 1==1%}UNICORN{%endif%}',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotEquals($oldContent, $newContent);
    }

    public function testSettingTheValueConditionallyBasedOnUserRole() {
        $resource = $this->getPhpBookResource();
        $oldContent = $resource->getContents();
        $this->usePluginWithResource(
            $resource,
            [
                [
                    'name' => 'repekaMetadataValueSetter',
                    'config' => [
                        'metadataName' => 'opis',
                        'metadataValue' => '{% if ("OPERATOR-" ~ resourceBeforeTransition.resourceClass) in command.executor.roles %}'
                            . '{{command.executor.userData.id}}{%endif%}',
                    ],
                ],
            ]
        );
        $newContent = $this->getPhpBookResource()->getContents();
        $this->assertNotEquals($oldContent, $newContent);
    }
}
