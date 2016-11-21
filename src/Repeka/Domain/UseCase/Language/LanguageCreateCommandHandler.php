<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;

class LanguageCreateCommandHandler {
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function handle(LanguageCreateCommand $command): Language {
        $language = $this->toLanguage($command);
        return $this->languageRepository->save($language);
    }

    private function toLanguage(LanguageCreateCommand $command): Language {
        $language = new Language($command->getFlag(), $command->getName());
        return $language;
    }
}
