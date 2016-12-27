<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class MetadataCreateCommandValidator extends CommandAttributesValidator {
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
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('label', Validator::notBlankInAllLanguages($this->availableLanguages))
            ->attribute('name', Validator::notBlank())
            ->attribute('control', Validator::in($this->supportedControls));
    }
}
