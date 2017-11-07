<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class XmlImportQueryValidator extends CommandAttributesValidator {
    /** @param XmlImportQuery $query */
    public function getValidator(Command $query): Validatable {
        return Validator::attribute('id', Validator::stringType()->digit());
    }
}
