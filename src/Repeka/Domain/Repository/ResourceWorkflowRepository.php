<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;

interface ResourceWorkflowRepository {
    public function get(ResourceEntity $resource): ResourceWorkflow;
}
