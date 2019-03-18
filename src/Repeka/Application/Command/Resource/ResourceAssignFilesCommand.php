<?php
namespace Repeka\Application\Command\Resource;

use Assert\Assertion;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\EventListener\UpdateDependentDisplayStrategiesListener;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\StringUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

/**
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.NPathComplexity")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceAssignFilesCommand extends Command {
    use Transactional;

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var CommandBus */
    private $commandBus;
    /** @var ResourceFileStorage */
    private $resourceFileStorage;
    /** @var FileSystemDriver */
    private $fileSystemDriver;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        ResourceRepository $resourceRepository,
        MetadataRepository $metadataRepository,
        CommandBus $commandBus,
        ResourceFileStorage $resourceFileStorage,
        FileSystemDriver $fileSystemDriver
    ) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
        $this->resourceFileStorage = $resourceFileStorage;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->metadataRepository = $metadataRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:resources:assign-files')
            ->setDescription('Assigns specified files to the metadata.')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED)
            ->addOption('paths', 'p', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        UpdateDependentDisplayStrategiesListener::$alwaysLeaveDirty = true;
        $yaml = new Parser();
        $filter = $yaml->parse($input->getOption('filter'));
        Assertion::notEmpty($filter, 'You have to specify some resources filter.');
        $paths = $input->getOption('paths');
        Assertion::notEmpty($paths, 'You have to specify some paths to assign.');
        $paths = array_map([$yaml, 'parse'], $paths);
        $offset = $input->getOption('offset') ?? 0;
        $limit = $input->getOption('limit') ?? 100;
        $query = ResourceListQuery::builder()
            ->filterByContents($filter)
            ->sortBy([['columnId' => 'id', 'direction' => 'ASC']])
            ->setPage(intval($offset / $limit) + 1)
            ->setResultsPerPage($limit)
            ->build();
        $resources = $this->resourceRepository->findByQuery($query);
        $metadataList = [];
        foreach ($paths as $pathSpec) {
            $metadataName = StringUtils::normalizeEntityName(key($pathSpec));
            $metadataList[$metadataName] = $this->metadataRepository->findByName($metadataName);
        }
        $progress = new ProgressBar($output, count($resources));
        $progress->display();
        $dryRun = $input->getOption('dry-run');
        $dryRunSummary = [];
        foreach ($resources as $resource) {
            $progress->advance();
            $contents = $resource->getContents();
            foreach ($paths as $pathSpec) {
                $metadataName = StringUtils::normalizeEntityName(key($pathSpec));
                $path = current($pathSpec);
                $resourcePath = $this->resourceFileStorage->getFileSystemPath($resource, $path);
                if ($matchingFiles = $this->fileSystemDriver->glob($resourcePath)) {
                    $matchingFiles = array_map(
                        function ($path) use ($resource) {
                            return $this->resourceFileStorage->getResourcePath($resource, $path);
                        },
                        $matchingFiles
                    );
                    $metadata = $metadataList[$metadataName];
                    if ($dryRun) {
                        $dryRunSummary[$resource->getId()][$metadataName] = $matchingFiles;
                    } else {
                        $contents = $contents->withMergedValues($metadata, $matchingFiles)->clearDuplicates($metadataList[$metadataName]);
                    }
                }
            }
            if ($contents != $resource->getContents()) {
                FirewallMiddleware::bypass(
                    function () use ($contents, $resource) {
                        $command = ResourceGodUpdateCommand::builder()
                            ->setResource($resource)
                            ->setNewContents($contents)
                            ->build();
                        $this->commandBus->handle($command);
                    }
                );
            }
        }
        $progress->finish();
        if ($dryRun) {
            $output->writeln("\nThis files would be assigned:");
            foreach ($dryRunSummary as $resourceId => $files) {
                $output->writeln($resourceId);
                foreach ($files as $metadataName => $filesToAdd) {
                    $output->writeln('  ' . $metadataName . ': ' . implode(', ', $filesToAdd));
                }
            }
        }
    }
}
