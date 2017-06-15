<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ApplicationInitializer {
    abstract public function initialize(OutputInterface $output, ContainerInterface $container);

    /**
     * By default, Doctrine overrides manually set ids with the generated ones for new entities.
     * This behaviour should be overridden for now because we set ids manually.
     *
     * @see http://stackoverflow.com/a/17587008/878514
     */
    protected function preventGeneratingIds(ClassMetadata $classMetadata) {
        $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $classMetadata->setIdGenerator(new AssignedGenerator());
    }

    protected function restoreUuidGenerator(ClassMetadata $classMetadata) {
        $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_UUID);
        $classMetadata->setIdGenerator(new UuidGenerator());
    }

    protected function restoreIdGenerator(ClassMetadata $classMetadata, string $sequenceName) {
        $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_SEQUENCE);
        $classMetadata->setIdGenerator(new SequenceGenerator($sequenceName, 1));
    }

    public function preEntityInitializer() {
    }

    public function postEntityInitializer() {
    }
}
