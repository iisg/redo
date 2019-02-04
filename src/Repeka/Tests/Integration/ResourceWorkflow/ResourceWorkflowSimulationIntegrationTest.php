<?php
namespace Repeka\Tests\Integration\ResourceWorkflow;

use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\MetadataFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\ResourceWorkflowsFixture;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourceWorkflowSimulationIntegrationTest extends IntegrationTestCase {
    /** @var ResourceWorkflow $workflow */
    private $workflow;

    protected function initializeDatabaseForTests() {
        self::loadFixture(new AdminAccountFixture(), new MetadataFixture(), new ResourceWorkflowsFixture());
    }

    /** @before */
    public function init() {
        $this->workflow = $this->container->get(ResourceWorkflowRepository::class)->findAll()[0];
    }

    public function testSimulationFromInitialState() {
        $command = new ResourceWorkflowSimulateCommand(
            $this->workflow->getPlaces(),
            $this->workflow->getTransitions()
        );
        $result = $this->handleCommandBypassingFirewall($command);
        $this->assertCount(1, $result['places']);
        $this->assertEquals('Imported', $result['places'][0]->getLabel()['EN']);
        $this->assertCount(1, $result['transitions']);
        $this->assertEquals('Attach metrics', $result['transitions'][0]->getLabel()['EN']);
    }

    public function testSimulationFromSomeState() {
        $command = new ResourceWorkflowSimulateCommand(
            $this->workflow->getPlaces(),
            $this->workflow->getTransitions(),
            [$this->workflow->getPlaces()[1]->getId()]
        );
        $result = $this->handleCommandBypassingFirewall($command);
        $this->assertCount(1, $result['places']);
        $this->assertEquals('Ready to scan', $result['places'][0]->getLabel()['EN']);
        $this->assertCount(1, $result['transitions']);
        $this->assertEquals('Scan', $result['transitions'][0]->getLabel()['EN']);
    }

    public function testSimulatingTransition() {
        $command = new ResourceWorkflowSimulateCommand(
            $this->workflow->getPlaces(),
            $this->workflow->getTransitions(),
            [$this->workflow->getPlaces()[1]->getId()],
            $this->workflow->getTransitions()[1]->getId()
        );
        $result = $this->handleCommandBypassingFirewall($command);
        $this->assertCount(1, $result['places']);
        $this->assertEquals('Scanned', $result['places'][0]->getLabel()['EN']);
        $this->assertCount(2, $result['transitions']);
        $this->assertEquals('Reject', $result['transitions'][0]->getLabel()['EN']);
        $this->assertEquals('Verify', $result['transitions'][1]->getLabel()['EN']);
    }

    public function testSimulatingInvalidTransition() {
        $this->expectException(\Exception::class);
        $command = new ResourceWorkflowSimulateCommand(
            $this->workflow->getPlaces(),
            $this->workflow->getTransitions(),
            [$this->workflow->getPlaces()[1]->getId()],
            $this->workflow->getTransitions()[0]->getId()
        );
        $this->handleCommandBypassingFirewall($command);
    }
}
