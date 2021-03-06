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

    /** @return string[] */
    public function getAvailableLanguageCodes(): array {
        $availableLanguages = $this->findAll();
        return array_map(
            function (Language $language) {
                return $language->getCode();
            },
            $availableLanguages
        );
    }

    public function findOne(string $code): Language {
        /** @var Language $language */
        $language = $this->find($code);
        if (!$language) {
            throw new EntityNotFoundException($this, $code);
        }
        return $language;
    }

    public function exists(string $code): bool {
        return !!$this->find($code);
    }

    public function delete(string $code): void {
        $language = $this->findOne($code);
        $this->getEntityManager()->remove($language);
    }
}
