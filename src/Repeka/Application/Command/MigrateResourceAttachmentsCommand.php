<?php
namespace Repeka\Application\Command;

use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResourceAttachmentsCommand extends ContainerAwareCommand {
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
        $resourceRepository = $this->getContainer()->get('repository.resource');
        $resources = $resourceRepository->findAll();
        $migratableResources = $this->getMigratableResources($resources, $output);
        if (count($migratableResources) == count($resources) || $nonAtomic) {
            $this->migrateResources($migratableResources, $output);
            $this->pruneDirectoryTree($output);
        }
    }

    private function getMigratableResources(array $resources, OutputInterface $output):array {
        $attachmentHelper = $this->getContainer()->get('repeka.upload.resource_attachment_helper');
        $migratableResources = [];
        $allMigrationsPossible = true;
        foreach ($resources as $resource) {
            /** @var ResourceEntity $resource */
            $existingFiles = $attachmentHelper->getFilesThatWouldBeOverwrittenInDestinationPaths($resource);
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
        $attachmentHelper = $this->getContainer()->get('repeka.upload.resource_attachment_helper');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $movedFilesCount = 0;
        $migratedResourceCount = 0;
        foreach ($migratableResources as $resource) {
            $partialMovedCount = $attachmentHelper->moveFilesToDestinationPaths($resource);
            $movedFilesCount += $partialMovedCount;
            if ($partialMovedCount > 0) {
                $migratedResourceCount += 1;
                $em->persist($resource);
            }
        }
        $em->flush();
        $output->writeln("<info>Moved $movedFilesCount files attached to $migratedResourceCount resources.</info>");
    }

    private function pruneDirectoryTree(OutputInterface $output): void {
        $attachmentPathGenerator = $this->getContainer()->get('repeka.upload.resource_attachment_path_generator');
        $deletedCount = 0;
        $directoryIterator = new \RecursiveDirectoryIterator(
            $attachmentPathGenerator->getUploadsRootPath(),
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
