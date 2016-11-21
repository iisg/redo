<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Language;

interface LanguageRepository {
    /**
     * @return Language[]
     */
    public function findAll();

    public function save(Language $language): Language;
}
