<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowStrategyFactory;

class ResourceWorkflowStrategyInjector {
    /** @var ResourceWorkflowStrategyFactory */
    private $workflowStrategyFactory;

    public function __construct(ResourceWorkflowStrategyFactory $workflowStrategyFactory) {
        $this->workflowStrategyFactory = $workflowStrategyFactory;
    }

    public function postLoad(LifecycleEventArgs $eventArgs) {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof ResourceWorkflow) {
            $this->workflowStrategyFactory->setForWorkflow($entity);
        }
    }
}
