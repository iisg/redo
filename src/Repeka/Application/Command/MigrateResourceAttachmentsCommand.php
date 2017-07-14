<?php

namespace Repeka\Application\Command;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Upload\ResourceAttachmentPathGenerator;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceAttachmentHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResourceAttachmentsCommand extends Command {
    /** @var ResourceAttachmentHelper */
    private $resourceAttachmentHelper;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceAttachmentPathGenerator */
    private $resourceAttachmentPathGenerator;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ResourceRepository $resourceRepository,
        ResourceAttachmentHelper $resourceAttachmentHelper,
        ResourceAttachmentPathGenerator $resourceAttachmentPathGenerator,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->resourceAttachmentHelper = $resourceAttachmentHelper;
        $this->resourceAttachmentPathGenerator = $resourceAttachmentPathGenerator;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        // @codingStandardsIgnoreStart
        $this
            ->setName('repeka:resources:migrate-attachments')
            ->setDescription('Ensures all attachments are in their destination paths.')
            ->addOption('nonatomic', null, null, "Migrate as much resources as possible, even if some have attachments that can't be migrated.");
        // @codingStandardsIgnoreEnd
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $nonAtomic = $input->getOption('nonatomic');
        $resources = $this->resourceRepository->findAll();
        $migratableResources = $this->getMigratableResources($resources, $output);
        if (count($migratableResources) == count($resources) || $nonAtomic) {
            $this->migrateResources($migratableResources, $output);
            $this->pruneDirectoryTree($output);
        }
    }

    private function getMigratableResources(array $resources, OutputInterface $output): array {
        $migratableResources = [];
        $allMigrationsPossible = true;
        foreach ($resources as $resource) {
            /** @var ResourceEntity $resource */
            $existingFiles = $this->resourceAttachmentHelper->getFilesThatWouldBeOverwrittenInDestinationPaths($resource);
            if (count($existingFiles) == 0) {
                $migratableResources[] = $resource;
            } else {
                $resourceId = $resource->getId();
                if ($allMigrationsPossible) {
                    $allMigrationsPossible = false;
                    $output->writeln("<error>Some resources can't be migrated!</error>");
                }
                $output->writeln("  Resource #$resourceId:");
                foreach ($existingFiles as $source => $target) {
                    $output->writeln("    $source\t=>  $target");
                }
            }
        }
        return $migratableResources;
    }

    private function migrateResources(array $migratableResources, OutputInterface $output): void {
        $movedFilesCount = 0;
        $migratedResourceCount = 0;
        foreach ($migratableResources as $resource) {
            $partialMovedCount = $this->resourceAttachmentHelper->moveFilesToDestinationPaths($resource);
            $movedFilesCount += $partialMovedCount;
            if ($partialMovedCount > 0) {
                $migratedResourceCount += 1;
                $this->entityManager->persist($resource);
            }
        }
        $this->entityManager->flush();
        $output->writeln("<info>Moved $movedFilesCount files attached to $migratedResourceCount resources.</info>");
    }

    private function pruneDirectoryTree(OutputInterface $output): void {
        $deletedCount = 0;
        $directoryIterator = new \RecursiveDirectoryIterator(
            $this->resourceAttachmentPathGenerator->getUploadsRootPath(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $flatDirectoryIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST, // delete children first, so that parent is potentially emptied and deleted too
            \RecursiveIteratorIterator::CATCH_GET_CHILD // ignore permission errors
        );
        foreach ($flatDirectoryIterator as $path => $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if ($fileInfo->isDir()) {
                $deletedCount += $this->deleteDirectoryIfEmpty($path);
            }
        }
        $output->writeln("<info>Deleted $deletedCount empty folders.</info>");
    }

    private function deleteDirectoryIfEmpty(string $path): bool {
        if (is_readable($path) && $this->isDirectoryEmpty($path)) {
            rmdir($path);
            return true;
        }
        return false;
    }

    private function isDirectoryEmpty(string $path): bool {
        return count(scandir($path)) == 2; // . and ..
    }
}
