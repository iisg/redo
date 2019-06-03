<?php
namespace Repeka\Tests\Integration\Serialization;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Serializer\Serializer;

class ResourceWorkflowNormalizerIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceWorkflow */
    private $workflow;
    /** @var Serializer */
    private $serializer;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->workflow = $this->getPhpBookResource()->getWorkflow();
        $this->serializer = $this->container->get('serializer');
    }

    public function testNoPluginsConfigForUnauthenticatedUser() {
        $normalized = $this->serializer->normalize($this->workflow);
        $this->assertArrayNotHasKey('pluginsConfig', $normalized['places'][0]);
    }

    public function testNoPluginsConfigForNonAdminUser() {
        $this->simulateAuthentication($this->getBudynekUser());
        $normalized = $this->serializer->normalize($this->workflow);
        $this->assertArrayNotHasKey('pluginsConfig', $normalized['places'][0]);
    }

    public function testHasPluginsConfigForAdminUser() {
        $this->simulateAuthentication($this->getAdminUser());
        $normalized = $this->serializer->normalize($this->workflow);
        $this->assertArrayHasKey('pluginsConfig', $normalized['places'][0]);
    }
}
