<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var string[] */
    private $availableLanguages;
    /** @var MetadataCreateCommandValidator */
    private $metadataCreateCommandValidator;

    public function __construct(LanguageRepository $languageRepository, MetadataCreateCommandValidator $metadataCreateCommandValidator) {
        $this->availableLanguages = $languageRepository->getAvailableLanguageCodes();
        $this->metadataCreateCommandValidator = $metadataCreateCommandValidator;
    }

    public function getValidator(): \Respect\Validation\Validator {
        return Validator
            ::attribute('label', Validator::notBlankInAllLanguages($this->availableLanguages))
            ->attribute('metadataList', Validator::arrayType()->length(1)->each(Validator::allOf(
                Validator::arrayType(),
                Validator::length(1),
                Validator::key('base_id', Validator::intVal())
            )));
    }
}
