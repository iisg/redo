<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Validator;

class ResourceUpdateContentsCommandValidator extends CommandAttributesValidator {
    /** @var ValueSetMatchesResourceKindRule */
    private $valueSetMatchesResourceKindRule;
    /** @var MetadataValuesSatisfyConstraintsRule */
    private $metadataValuesSatisfyConstraintsRule;

    /** @SuppressWarnings("PHPMD.LongVariable") */
    public function __construct(
        ValueSetMatchesResourceKindRule $valueSetMatchesResourceKindRule,
        MetadataValuesSatisfyConstraintsRule $metadataValuesSatisfyConstraintsRule
    ) {
        $this->valueSetMatchesResourceKindRule = $valueSetMatchesResourceKindRule;
        $this->metadataValuesSatisfyConstraintsRule = $metadataValuesSatisfyConstraintsRule;
    }

    /**
     * @param ResourceUpdateContentsCommand $command
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('resource', Validator::instance(ResourceEntity::class)->callback(function (ResourceEntity $r) {
                return $r->getId() > 0;
            }))
            ->attribute('contents', Validator::length(1))
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getResource()->getKind()))
            ->attribute('contents', $this->metadataValuesSatisfyConstraintsRule->forResourceKind($command->getResource()->getKind()));
    }
}
