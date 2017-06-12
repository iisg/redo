<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;

class ResourceWorkflowDriverInjector {
    /** @var ResourceWorkflowDriverFactory */
    private $workflowDriverFactory;

    public function __construct(ResourceWorkflowDriverFactory $workflowDriverFactory) {
        $this->workflowDriverFactory = $workflowDriverFactory;
    }

    public function postLoad(LifecycleEventArgs $eventArgs) {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof ResourceWorkflow) {
            $this->workflowDriverFactory->setForWorkflow($entity);
        }
    }
}
