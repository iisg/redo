<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class MetadataUpdateCommandValidator extends CommandAttributesValidator {
    /** @var array */
    private $availableLanguages;

    public function __construct(LanguageRepository $languageRepository) {
        $this->availableLanguages = $languageRepository->getAvailableLanguageCodes();
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('metadataId', Validator::intVal()->min(1))
            ->attribute('newLabel', Validator::containsOnlyAvailableLanguages($this->availableLanguages))
            ->attribute('newPlaceholder', Validator::containsOnlyAvailableLanguages($this->availableLanguages))
            ->attribute('newDescription', Validator::containsOnlyAvailableLanguages($this->availableLanguages));
    }
}
