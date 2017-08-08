<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceCreateCommandValidator extends CommandAttributesValidator {
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

    /** @param ResourceCreateCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('kind', Validator::instance(ResourceKind::class)->callback(function (ResourceKind $rk) {
                return $rk->getId() > 0;
            }))
            ->attribute('contents', Validator::arrayType()->notEmpty()->each(Validator::arrayType()))
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getKind()))
            ->attribute('contents', $this->metadataValuesSatisfyConstraintsRule->forResourceKind($command->getKind()));
    }
}
