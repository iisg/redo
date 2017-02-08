<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowDoctrineRepository extends EntityRepository implements ResourceWorkflowRepository {
    public function save(ResourceWorkflow $workflow): ResourceWorkflow {
        $this->getEntityManager()->persist($workflow);
        return $workflow;
    }

    public function findOne($id): ResourceWorkflow {
        /** @var ResourceWorkflow $workflow */
        $workflow = $this->find($id);
        if (!$workflow) {
            throw new EntityNotFoundException("ID: $id");
        }
        return $workflow;
    }
}
