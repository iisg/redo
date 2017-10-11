<?php
namespace Repeka\Application\Repository;

use Assert\Assertion;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Repeka\Domain\Entity\Metadata;
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
            throw new EntityNotFoundException($this, $id);
        }
        return $workflow;
    }

    public function delete(ResourceWorkflow $resourceWorkflow): void {
        $this->getEntityManager()->remove($resourceWorkflow);
    }

    /**
     * @param Metadata|int $metadata
     * @return ResourceWorkflow[]
     */
    public function findByAssigneeMetadata($metadata): array {
        Assertion::true(is_numeric($metadata) || $metadata instanceof Metadata);
        if (is_numeric($metadata)) {
            $baseId = $metadata;
        } else {
            $baseId = $metadata->isBase() ? $metadata->getId() : $metadata->getBaseId();
        }
        $resultSetMapping = $this->getResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(<<<SQL
            SELECT * FROM workflow WHERE id IN (
              SELECT DISTINCT workflow_id
              FROM (SELECT jsonb_array_elements(places) AS place, id AS workflow_id FROM workflow) AS exploded
              WHERE place->'assigneeMetadataIds' @> :metadata::VARCHAR::JSONB
            );
SQL
            , $resultSetMapping);
        $query->setParameter('metadata', $baseId);
        return $query->getArrayResult();
    }

    private function getResultSetMapping(): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(ResourceWorkflow::class, 'w');
        return $rsm;
    }
}
