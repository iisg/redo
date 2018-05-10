<?php
namespace Repeka\Application\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * By default, Doctrine overrides manually set ids with the generated ones for new entities.
 * This class allows to override this behaviour whenever we set ids manually.
 *
 * @see http://stackoverflow.com/a/17587008/878514
 */
class EntityIdGeneratorHelper {
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function preventGeneratingIds(string $className) {
        $classMetadata = $this->getClassMetadata($className);
        $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $classMetadata->setIdGenerator(new AssignedGenerator());
    }

    public function restoreIdGenerator(string $className, string $sequenceName) {
        $classMetadata = $this->getClassMetadata($className);
        $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_SEQUENCE);
        $classMetadata->setIdGenerator(new SequenceGenerator($sequenceName, 1));
    }

    private function getClassMetadata(string $className) {
        return $this->entityManager->getClassMetadata($className);
    }
}
