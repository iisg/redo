<?php
namespace Repeka\Tests\Integration\ResourceWorkflow;

use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\MetadataFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\ResourceWorkflowsFixture;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulationResource;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourceWorkflowDriverInjectorIntegrationTest extends IntegrationTestCase {
    /** @var ResourceWorkflow $workflow */
    private $workflow;
    private $resourceClass;

    protected function initializeDatabaseForTests() {
        self::loadFixture(new AdminAccountFixture(), new MetadataFixture(), new ResourceWorkflowsFixture());
    }

    /** @before */
    public function init() {
        $this->workflow = $this->container->get(ResourceWorkflowRepository::class)->findAll()[0];
        $this->resourceClass = 'books';
    }

    public function testTheDriverIsInjected() {
        $places = $this->workflow->getPlaces(new ResourceWorkflowSimulationResource());
        $this->assertCount(1, $places);
        $this->assertEquals('Zaimportowana', $places[0]->getLabel()['PL']);
    }

    public function testTheDriverIsInjectedForWorkflowThatBelongsToResourceKind() {
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand('a', ['PL' => 'a', 'EN' => 'a'], [['id' => 1]], $this->workflow)
        );
        $resourceKind = $this->container->get(ResourceKindRepository::class)->findOne($resourceKind->getId());
        $places = $resourceKind->getWorkflow()->getPlaces(new ResourceWorkflowSimulationResource());
        $this->assertCount(1, $places);
    }
}
