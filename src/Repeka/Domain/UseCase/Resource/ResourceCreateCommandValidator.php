<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Validator;

class ResourceCreateCommandValidator extends CommandAttributesValidator {
    /** @var ValueSetMatchesResourceKindRule */
    private $valueSetMatchesResourceKindRule;

    public function __construct(ValueSetMatchesResourceKindRule $valueSetMatchesResourceKindRule) {
        $this->valueSetMatchesResourceKindRule = $valueSetMatchesResourceKindRule;
    }

    /**
     * @param ResourceCreateCommand $command
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('kind', Validator::instance(ResourceKind::class)->callback(function (ResourceKind $rk) {
                return $rk->getId() > 0;
            }))
            ->attribute('contents', Validator::length(1))
            ->attribute('contents', $this->valueSetMatchesResourceKindRule->forResourceKind($command->getKind()));
    }
}
