<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\LockedMetadataValuesAreUnchangedRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceUpdateContentsCommandValidator extends CommandAttributesValidator {
    /** @var ValueSetMatchesResourceKindRule */
    private $valueSetMatchesResourceKindRule;
    /** @var MetadataValuesSatisfyConstraintsRule */
    private $metadataValuesSatisfyConstraintsRule;
    /** @var LockedMetadataValuesAreUnchangedRule */
    private $lockedMetadataValuesAreUnchangedRule;
    /** @var ResourceContentsCorrectStructureRule */
    private $resourceContentsCorrectStructureRule;

    /** @SuppressWarnings("PHPMD.LongVariable") */
    public function __construct(
        ValueSetMatchesResourceKindRule $valueSetMatchesResourceKindRule,
        MetadataValuesSatisfyConstraintsRule $metadataValuesSatisfyConstraintsRule,
        LockedMetadataValuesAreUnchangedRule $lockedMetadataValuesAreUnchangedRule,
        ResourceContentsCorrectStructureRule $resourceContentsCorrectStructureRule
    ) {
        $this->valueSetMatchesResourceKindRule = $valueSetMatchesResourceKindRule;
        $this->metadataValuesSatisfyConstraintsRule = $metadataValuesSatisfyConstraintsRule;
        $this->lockedMetadataValuesAreUnchangedRule = $lockedMetadataValuesAreUnchangedRule;
        $this->resourceContentsCorrectStructureRule = $resourceContentsCorrectStructureRule;
    }

    /**
     * @param ResourceUpdateContentsCommand $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resource', Validator::instance(ResourceEntity::class)->callback(function (ResourceEntity $r) {
                return $r->getId() > 0;
            }))
            ->attribute('contents', $this->resourceContentsCorrectStructureRule)
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getResource()->getKind()))
            ->attribute('contents', $this->metadataValuesSatisfyConstraintsRule->forResourceKind($command->getResource()->getKind()))
            ->attribute('contents', $this->lockedMetadataValuesAreUnchangedRule->forResource($command->getResource()));
    }
}
