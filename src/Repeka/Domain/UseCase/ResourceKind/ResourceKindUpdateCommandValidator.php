<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\Validation\Validator;

class ResourceKindUpdateCommandValidator extends ResourceKindCreateCommandValidator {
    public function __construct(LanguageRepository $languageRepository, MetadataCreateCommandValidator $metadataCreateCommandValidator) {
        parent::__construct($languageRepository, $metadataCreateCommandValidator);
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return parent::getValidator($command)
            ->attribute('resourceKindId', Validator::intVal()->min(1));
    }
}
