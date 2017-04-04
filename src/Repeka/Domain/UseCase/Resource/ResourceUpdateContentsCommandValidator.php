<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Validator;

class ResourceUpdateContentsCommandValidator extends CommandAttributesValidator {
    /** @var ValueSetMatchesResourceKindRule */
    private $valueSetMatchesResourceKindRule;

    public function __construct(ValueSetMatchesResourceKindRule $valueSetMatchesResourceKindRule) {
        $this->valueSetMatchesResourceKindRule = $valueSetMatchesResourceKindRule;
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
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getResource()->getKind()));
    }
}
