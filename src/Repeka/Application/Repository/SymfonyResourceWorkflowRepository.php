<?php
namespace Repeka\Application\Repository;

use Repeka\Application\Entity\SymfonyResourceWorkflow;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SymfonyResourceWorkflowRepository implements ResourceWorkflowRepository {
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function get(ResourceEntity $resource): ResourceWorkflow {
        $workflow = $this->container->get('state_machine.scanning'); // TODO should depend on the resource
        return new SymfonyResourceWorkflow($workflow);
    }
}
