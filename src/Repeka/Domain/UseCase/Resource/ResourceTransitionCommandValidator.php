<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceTransitionCommandValidator extends CommandAttributesValidator {
    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;

    public function __construct(TransitionPossibilityChecker $transitionPossibilityChecker) {
        $this->transitionPossibilityChecker = $transitionPossibilityChecker;
    }

    /** @param ResourceTransitionCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('transitionId', Validator::notBlank())
            ->attribute('resource', Validator::allOf(
                Validator::instance(ResourceEntity::class),
                Validator::callback(function (ResourceEntity $resource) {
                    return $resource->getId() > 0;
                })->setTemplate('Resource ID must be greater than 0'),
                Validator::callback(function (ResourceEntity $resource) {
                    return $resource->hasWorkflow();
                })->setTemplate('Resource must have a workflow'),
                Validator::callback($this->assertHasTransition($command->getTransitionId()))
                    ->setTemplate('Given transitionId does not exist')
            ))->callback([$this, 'transitionIsPossible']);
    }

    private function assertHasTransition(string $transitionId) {
        return function (ResourceEntity $resource) use ($transitionId) {
            $transitions = $resource->getWorkflow()->getTransitions($resource);
            return in_array($transitionId, EntityUtils::mapToIds($transitions));
        };
    }

    public function transitionIsPossible(ResourceTransitionCommand $command): bool {
        $workflow = $command->getResource()->getWorkflow();
        $transition = $workflow->getTransition($command->getTransitionId());
        return $this->transitionPossibilityChecker
            ->check($command->getResource(), $transition, $command->getExecutor())
            ->isTransitionPossible();
    }
}
