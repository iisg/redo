<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\AssignmentFinder;

class NativeAssignmentFinder implements AssignmentFinder {
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /** @return ResourceEntity[] */
    public function findAssignedResources(User $user): array {
        $resultSetMapping = ResultSetMappings::resourceEntity($this->em);
        $query = $this->em->createNativeQuery(<<<SQL
SELECT *
FROM resource
WHERE id IN (
  SELECT resource_id
  FROM (
         SELECT
           resource_id,
           resource_contents -> metadata_id :: CHAR AS assignee_ids
         FROM (
                SELECT
                  jsonb_array_elements(place -> 'assigneeMetadataIds') AS metadata_id,
                  resource_id,
                  resource_contents
                FROM (
                       SELECT
                         jsonb_array_elements(places) AS place,
                         resource.id                  AS resource_id,
                         resource.contents            AS resource_contents
                       FROM workflow
                         LEFT JOIN resource_kind ON workflow.id = resource_kind.workflow_id
                         LEFT JOIN resource ON resource_kind.id = resource.kind_id
                     ) AS with_places_as_rows
              ) AS with_metadata
       ) AS with_assignee_ids
  WHERE assignee_ids @> :userId :: CHAR :: JSONB
)
SQL
            , $resultSetMapping);
        $query->setParameter('userId', $user->getUserData());
        return $query->getResult();
    }
}
