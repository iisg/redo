<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceEntity;

class ResourceCannotEnterPlaceException extends ResourceWorkflowException {
    public function __construct($placeIds, ResourceEntity $resource, \Exception $previous = null) {
        $placeIds = is_array($placeIds) ? implode(', ', $placeIds) : $placeIds;
        parent::__construct('resourceCannotEnterPlace', $resource->getWorkflow(), [
            'placeIds' => $placeIds,
            'resourceId' => $resource->getId(),
        ], $previous);
    }
}
