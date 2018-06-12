<?php
namespace Repeka\Tests\Integration;

use Repeka\Tests\IntegrationTestCase;

class RepekaExtensionIntegrationTest extends IntegrationTestCase {
    public function testReadingResourceClasses() {
        $resourceClasses = $this->container->getParameter('repeka.resource_classes');
        $this->assertContains('users', $resourceClasses);
    }

    public function testReadingResourceClassesConfig() {
        $resourceClasses = $this->container->getParameter('repeka.resource_classes_config');
        $this->assertArrayHasKey('users', $resourceClasses);
        foreach ($resourceClasses as $resourceClass) {
            $this->assertArrayHasKey('name', $resourceClass);
            $this->assertArrayHasKey('admins', $resourceClass);
            $this->assertArrayHasKey('operators', $resourceClass);
            $this->assertTrue(is_string($resourceClass['name']));
            $this->assertTrue(is_array($resourceClass['admins']));
            $this->assertTrue(is_array($resourceClass['operators']));
        }
    }
}
