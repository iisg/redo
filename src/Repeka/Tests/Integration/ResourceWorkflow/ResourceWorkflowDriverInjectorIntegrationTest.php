<?php

namespace Repeka\Tests\Integration\ResourceWorkflow;

use Repeka\DeveloperBundle\DataFixtures\ORM\MetadataFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\ResourceWorkflowsFixture;
use Repeka\DeveloperBundle\DataFixtures\ORM\RolesFixture;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceWorkflowDriverInjectorIntegrationTest extends IntegrationTestCase {
    /** @var ResourceWorkflow $workflow */
    private $workflow;
    private $resourceClass;

    public function setUp() {
        parent::setUp();
        self::loadFixture(new RolesFixture(), new MetadataFixture(), new ResourceWorkflowsFixture());
        $this->workflow = $this->container->get(ResourceWorkflowRepository::class)->findAll()[0];
        $this->resourceClass = 'books';
    }

    public function testTheDriverIsInjected() {
        $places = $this->workflow->getPlaces(new ResourceEntity(new ResourceKind([], 'books'), [], $this->resourceClass));
        $this->assertCount(1, $places);
        $this->assertEquals('Zaimportowana', $places[0]->getLabel()['PL']);
    }

    public function testTheDriverIsInjectedForWorkflowThatBelongsToResourceKind() {
        $resourceKind = $this->container->get(ResourceKindRepository::class)->save(new ResourceKind([], 'books', $this->workflow));
        $resourceKind = $this->container->get(ResourceKindRepository::class)->findOne($resourceKind->getId());
        $places = $resourceKind->getWorkflow()->getPlaces(new ResourceEntity(new ResourceKind([], 'books'), [], $this->resourceClass));
        $this->assertCount(1, $places);
    }
}
