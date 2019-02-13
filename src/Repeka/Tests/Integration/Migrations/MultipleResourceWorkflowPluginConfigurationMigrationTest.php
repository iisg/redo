<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180705073559
 */
class MultipleResourceWorkflowPluginConfigurationMigrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV0_9();
        $this->migrate();
    }

    public function testTheFirstPlaceInBookWorkflowHasValidPluginConfiguration() {
        $workflow = $this->getPhpBookResource()->getWorkflow();
        $firstPlace = $workflow->getInitialPlace();
        $pluginsConfig = $firstPlace->getPluginsConfig();
        $this->assertCount(1, $pluginsConfig);
        $config = $pluginsConfig[0];
        $this->assertTrue($config->isForPlugin(RepekaMetadataValueSetterResourceWorkflowPlugin::class));
        $this->assertEquals('Data utworzenia', $config->getConfigValue('metadataName'));
    }
}
