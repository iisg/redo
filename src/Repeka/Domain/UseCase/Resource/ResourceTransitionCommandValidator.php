<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceTransitionCommandValidator extends CommandAttributesValidator {
    /** @param ResourceTransitionCommand $command */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('transitionId', Validator::notBlank())
            ->attribute(
                'resource',
                Validator::allOf(
                    Validator::instance(ResourceEntity::class),
                    Validator::callback(function (ResourceEntity $resource) {
                        return $resource->getId() > 0;
                    })->setName('Resource ID must be greater than 0'),
                    Validator::callback(function (ResourceEntity $resource) {
                        return $resource->hasWorkflow();
                    })->setName('Resource must have a workflow'),
                    Validator::callback($this->assertHasTransition($command->getTransitionId()))
                        ->setName('Given transitionId does not exist')
                )
            );
    }

    private function assertHasTransition(string $transitionId) {
        return function (ResourceEntity $resource) use ($transitionId) {
            $transitions = $resource->getWorkflow()->getTransitions($resource);
            return in_array($transitionId, array_map(function (ResourceWorkflowTransition $transition) {
                return $transition->getId();
            }, $transitions));
        };
    }
}
