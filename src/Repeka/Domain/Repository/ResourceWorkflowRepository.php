<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceWorkflow;

interface ResourceWorkflowRepository {
    public function save(ResourceWorkflow $workflow): ResourceWorkflow;

    /** @return ResourceWorkflow[] */
    public function findAll();

    public function findOne($id): ResourceWorkflow;

    public function delete(ResourceWorkflow $resourceWorkflow): void;
}
