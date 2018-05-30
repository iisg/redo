<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\LockedMetadataValuesAreUnchangedRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ResourceDoesNotContainDuplicatedFilenamesRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceTransitionCommandValidator extends CommandAttributesValidator {
    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;
    /** @var ValueSetMatchesResourceKindRule */
    private $valueSetMatchesResourceKindRule;
    /** @var MetadataValuesSatisfyConstraintsRule */
    private $metadataValuesSatisfyConstraintsRule;
    /** @var ResourceContentsCorrectStructureRule */
    private $resourceContentsCorrectStructureRule;
    /** @var ResourceDoesNotContainDuplicatedFilenamesRule */
    private $resourceDoesNotContainDuplicatedFilenamesRule;
    /** @var LockedMetadataValuesAreUnchangedRule */
    private $lockedMetadataValuesAreUnchangedRule;

    public function __construct(
        TransitionPossibilityChecker $transitionPossibilityChecker,
        ValueSetMatchesResourceKindRule $valueSetMatchesResourceKindRule,
        MetadataValuesSatisfyConstraintsRule $metadataValuesSatisfyConstraintsRule,
        ResourceContentsCorrectStructureRule $resourceContentsCorrectStructureRule,
        ResourceDoesNotContainDuplicatedFilenamesRule $resourceDoesNotContainDuplicatedFilenamesRule,
        LockedMetadataValuesAreUnchangedRule $lockedMetadataValuesAreUnchangedRule
    ) {
        $this->transitionPossibilityChecker = $transitionPossibilityChecker;
        $this->valueSetMatchesResourceKindRule = $valueSetMatchesResourceKindRule;
        $this->metadataValuesSatisfyConstraintsRule = $metadataValuesSatisfyConstraintsRule;
        $this->resourceContentsCorrectStructureRule = $resourceContentsCorrectStructureRule;
        $this->resourceDoesNotContainDuplicatedFilenamesRule = $resourceDoesNotContainDuplicatedFilenamesRule;
        $this->lockedMetadataValuesAreUnchangedRule = $lockedMetadataValuesAreUnchangedRule;
    }

    /** @param ResourceTransitionCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('transitionOrId', Validator::notBlank())
            ->attribute(
                'resource',
                Validator::allOf(
                    Validator::callback(
                        function (ResourceEntity $resource) {
                            return $resource->getId() > 0 || $resource->getId() == null;
                        }
                    )->setTemplate('Resource ID must not be less than 0'),
                    Validator::callback($this->assertHasTransition($command->getTransition()->getId()))
                        ->setTemplate('Given transitionId does not exist')
                )
            )->callback([$this, 'transitionIsPossible'])
            ->attribute('contents', $this->resourceContentsCorrectStructureRule)
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getResource()->getKind()))
            ->attribute('contents', $this->metadataValuesSatisfyConstraintsRule->forResourceKind($command->getResource()->getKind()))
            ->attribute('contents', $this->resourceDoesNotContainDuplicatedFilenamesRule)
            ->attribute(
                'contents',
                $this->lockedMetadataValuesAreUnchangedRule->forResourceAndTransition($command->getResource(), $command->getTransition())
            );
    }

    private function assertHasTransition($transitionId) {
        return function (ResourceEntity $resource) use ($transitionId) {
            if (SystemTransition::isValid($transitionId)) {
                return true;
            } elseif ($resource->hasWorkflow()) {
                $transitions = $resource->getWorkflow()->getTransitions($resource);
                return in_array($transitionId, EntityUtils::mapToIds($transitions));
            } else {
                return false;
            }
        };
    }

    public function transitionIsPossible(ResourceTransitionCommand $command): bool {
        if (!$command->getExecutor()) {
            return true;
        }
        $transition = $command->getTransition();
        $this->transitionPossibilityChecker
            ->check($command->getResource(), $command->getContents(), $transition, $command->getExecutor())
            ->assertTransitionIsPossible();
        return true;
    }
}
