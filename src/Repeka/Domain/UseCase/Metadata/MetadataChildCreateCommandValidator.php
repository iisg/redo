<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class MetadataChildCreateCommandValidator extends CommandAttributesValidator {
    /** @var array */
    private $availableLanguages;
    /** @var array */
    private $supportedControls;

    public function __construct(LanguageRepository $languageRepository, array $supportedControls) {
        $this->availableLanguages = $languageRepository->getAvailableLanguageCodes();
        $this->supportedControls = $supportedControls;
    }

    /**
     * @inheritdoc
     * @param MetadataChildCreateCommand $command
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('newChildMetadata', Validator
                ::key('label', Validator::notBlankInAllLanguages($this->availableLanguages))
                ->key('name', Validator::notBlank())
                ->key('placeholder', Validator::containsOnlyAvailableLanguages($this->availableLanguages))
                ->key('description', Validator::containsOnlyAvailableLanguages($this->availableLanguages))
                ->key('control', Validator::in($this->supportedControls)));
    }
}
