<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceWorkflowCreateCommandValidator extends CommandAttributesValidator {
    /** @var array */
    private $availableLanguages;

    public function __construct(LanguageRepository $languageRepository) {
        $this->availableLanguages = $languageRepository->getAvailableLanguageCodes();
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator::attribute('name', Validator::notBlankInAllLanguages($this->availableLanguages));
    }
}
