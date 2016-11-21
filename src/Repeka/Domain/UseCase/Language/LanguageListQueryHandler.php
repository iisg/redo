<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;

class LanguageListQueryHandler {
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return Language[]
     */
    public function handle(): array {
        return $this->languageRepository->findAll();
    }
}
