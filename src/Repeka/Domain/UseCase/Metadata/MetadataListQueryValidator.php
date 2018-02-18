<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataListQueryValidator extends CommandAttributesValidator {
    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(ResourceClassExistsRule $resourceClassExistsRule) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @param MetadataListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClasses', Validator::arrayType()->each($this->resourceClassExistsRule))
            ->attribute('controls', Validator::arrayType()->each(Validator::instance(MetadataControl::class)));
    }
}
