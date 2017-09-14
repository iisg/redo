<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowDeleteCommandValidator extends CommandAttributesValidator {
    /** @var ResourceWorkflowRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute(
            'resourceWorkflow',
            Validator::callback([$this, 'isWorkflowNotUsed'])->setTemplate('workflow is assigned to one of resource kinds')
        );
    }

    public function isWorkflowNotUsed(ResourceWorkflow $resourceWorkflow): bool {
        return $this->resourceKindRepository->countByResourceWorkflow($resourceWorkflow) === 0;
    }
}
