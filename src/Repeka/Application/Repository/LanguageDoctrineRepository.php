<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;

class LanguageDoctrineRepository extends EntityRepository implements LanguageRepository {
    public function save(Language $language): Language {
        $this->getEntityManager()->persist($language);
        return $language;
    }
}
