<?php
namespace Repeka\Tests\Integration\ResourceWorkflow;

use Repeka\DeveloperBundle\DataFixtures\ORM\ResourceWorkflowsFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\RolesFixture;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Tests\IntegrationTestCase;

class ResourceWorkflowDriverInjectorIntegrationTest extends IntegrationTestCase {
    /** @var ResourceWorkflow $workflow */
    private $workflow;

    public function setUp() {
        parent::setUp();
        self::loadFixture(new RolesFixture(), new ResourceWorkflowsFixture());
        $this->workflow = $this->container->get('repository.workflow')->findAll()[0];
    }

    public function testTheDriverIsInjected() {
        $places = $this->workflow->getPlaces(new ResourceEntity(new ResourceKind([]), []));
        $this->assertCount(1, $places);
        $this->assertEquals('Zaimportowana', $places[0]->getLabel()['PL']);
    }

    public function testTheDriverIsInjectedForWorkflowThatBelongsToResourceKind() {
        $resourceKind = $this->container->get('repository.resource_kind')->save(new ResourceKind([], $this->workflow));
        $resourceKind = $this->container->get('repository.resource_kind')->findOne($resourceKind->getId());
        $places = $resourceKind->getWorkflow()->getPlaces(new ResourceEntity(new ResourceKind([]), []));
        $this->assertCount(1, $places);
    }
}
