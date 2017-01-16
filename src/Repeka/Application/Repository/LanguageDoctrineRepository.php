<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\LanguageRepository;

class LanguageDoctrineRepository extends EntityRepository implements LanguageRepository {
    public function save(Language $language): Language {
        $this->getEntityManager()->persist($language);
        return $language;
    }

    /**
     * @return string[]
     */
    public function getAvailableLanguageCodes(): array {
        $availableLanguages = $this->findAll();
        return array_map(function (Language $language) {
            return $language->getCode();
        }, $availableLanguages);
    }

    public function findOne(string $code): Language {
        $language = $this->find($code);
        if (!$language) {
            throw new EntityNotFoundException("Code: $code");
        }
        return $language;
    }
}
