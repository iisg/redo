<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\LanguageRepository;
use Respect\Validation\Validator;

class SystemLanguageExistsConstraint extends RespectValidationMetadataConstraint {
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::SYSTEM_LANGUAGE];
    }

    protected function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::in($this->languageRepository->getAvailableLanguageCodes());
    }
}
