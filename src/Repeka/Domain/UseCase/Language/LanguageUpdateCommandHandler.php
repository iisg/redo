<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;

class LanguageUpdateCommandHandler {
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function handle(LanguageUpdateCommand $command): Language {
        $language = $this->languageRepository->findOne($command->getLanguageCode());
        $language->update($command->getNewFlag(), $command->getNewName());
        return $this->languageRepository->save($language);
    }
}
