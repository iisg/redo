<?php
namespace Repeka\Application\Command;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Upload\BasicResourceFileHelper;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResourceFilesCommand extends Command {
    /** @var BasicResourceFileHelper */
    private $resourceFileHelper;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFilePathGenerator */
    private $resourceFilePathGenerator;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var DirectoryContentsLister */
    private $directoryContentsLister;

    public function __construct(
        ResourceRepository $resourceRepository,
        BasicResourceFileHelper $resourceFileHelper,
        ResourceFilePathGenerator $resourceFilePathGenerator,
        EntityManagerInterface $entityManager,
        DirectoryContentsLister $directoryContentsLister
    ) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->resourceFileHelper = $resourceFileHelper;
        $this->resourceFilePathGenerator = $resourceFilePathGenerator;
        $this->entityManager = $entityManager;
        $this->directoryContentsLister = $directoryContentsLister;
    }

    protected function configure() {
        // @codingStandardsIgnoreStart
        $this
            ->setName('repeka:resources:migrate-files')
            ->setDescription('Ensures all files are in their destination paths.')
            ->addOption('nonatomic', null, null, "Migrate as much resources as possible, even if some have files that can't be migrated.");
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
            $existingFiles = $this->resourceFileHelper->getFilesThatWouldBeOverwrittenInDestinationPaths($resource);
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
            $partialMovedCount = $this->resourceFileHelper->moveFilesToDestinationPaths($resource);
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
        $uploadsDir = $this->resourceFilePathGenerator->getUploadsRootPath();
        $foldersToDelete = $this->directoryContentsLister->listSubfoldersRecursively($uploadsDir);
        foreach ($foldersToDelete as $fileInfo) {
            if ($fileInfo->isDir()) {
                $deletedCount += $this->deleteDirectoryIfEmpty($fileInfo->getPathname());
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
