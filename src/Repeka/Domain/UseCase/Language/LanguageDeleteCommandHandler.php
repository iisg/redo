<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Repository\LanguageRepository;

class LanguageDeleteCommandHandler {
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function handle(LanguageDeleteCommand $command): void {
        $this->languageRepository->delete($command->getCode());
    }
}
