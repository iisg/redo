<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Exception\EntityNotFoundException;

interface LanguageRepository {
    /**
     * @return Language[]
     */
    public function findAll();

    /**
     * @return string[]
     */
    public function getAvailableLanguageCodes(): array;

    /**
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findOne(string $code): Language;

    public function save(Language $language): Language;
}
