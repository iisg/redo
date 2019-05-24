<?php
namespace Repeka\Plugins\Redo\Command\KohaImport;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQuery;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Repeka\Plugins\Redo\Service\KohaXmlResourceDownloader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class KohaImportCommand extends ContainerAwareCommand {
    use CommandBusAware;

    /** @var ImportConfigFactory */
    private $importConfigFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var KohaXmlResourceDownloader */
    private $downloader;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        ImportConfigFactory $importConfigFactory,
        ResourceKindRepository $resourceKindRepository,
        ResourceRepository $resourceRepository,
        KohaXmlResourceDownloader $downloader,
        MetadataRepository $metadataRepository
    ) {
        parent::__construct();
        $this->importConfigFactory = $importConfigFactory;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
        $this->downloader = $downloader;
        $this->metadataRepository = $metadataRepository;
    }

    protected function configure() {
        $this
            ->setName('redo:koha:import')
            ->addOption('resourceKindId', 'r', InputOption::VALUE_REQUIRED)
            ->addOption('parentId', 'p', InputOption::VALUE_OPTIONAL)
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL)
            ->addOption('barcodeMetadataId', 'm', InputOption::VALUE_OPTIONAL)
            ->addOption('barcode', 'b', InputOption::VALUE_OPTIONAL)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, '', 0)
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, '', 50)
            ->setDescription('Imports data from Koha.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $noBarcode = 'no barcode';
        $imported = 'imported';
        $error = 'error';
        $cannotImportFromBarcode = 'cannot import from barcode';
        $otherErrorDuringImport = 'other error during download';
        $stats = [
            $imported => 0,
            $error => 0,
        ];
        $resourceIdStats = [
            $noBarcode => [],
            $cannotImportFromBarcode => [],
            $otherErrorDuringImport => [],
        ];
        $config = $input->getOption('config') ?? __DIR__ . '/../../Tests/Integration/MetadataImport/dumps/marc-import-config.yml';
        $barcodeMetadata = $this->metadataRepository->findByNameOrId($input->getOption('barcodeMetadataId') ?? 'barkod');
        $offset = $input->getOption('offset');
        $limit = $input->getOption('limit');
        $builder = new ResourceListQueryBuilder();
        if ($resourceKindNameOrId = $input->getOption('resourceKindId')) {
            $resourceKind = $this->resourceKindRepository->findByNameOrId($resourceKindNameOrId);
            $builder = $builder->filterByResourceKind($resourceKind);
        }
        if ($parentId = $input->getOption('parentId')) {
            $builder = $builder->filterByContents([SystemMetadata::PARENT => $parentId]);
        }
        if ($barcode = $input->getOption('barcode')) {
            $builder = $builder->filterByContents([$barcodeMetadata->getId() => $barcode]);
        }
        if ($barcode = $input->getOption('limit')) {
            $builder = $builder->setResultsPerPage($limit);
        }
        if ($barcode = $input->getOption('offset')) {
            $builder = $builder->setPage(($offset / $limit) + 1);
        }
        $query = $builder->build();
        $resources = $this->resourceRepository->findByQuery($query);
        $output->writeln(sprintf("%d resources to import", $resources->count()));
        $progressBar = new ProgressBar($output, $resources->count());
        $importConfig = $this->importConfigFactory->fromFile($config, $resourceKind);
        foreach ($resources as $resource) {
            $barcode = $resource->getContents()->getValuesWithoutSubmetadata($barcodeMetadata);
            if (!empty($barcode)) {
                $barcode = $barcode[0];
                $resourceXml = $this->downloader->downloadById($barcode);
                if ($resourceXml === null) {
                    $stats[$error]++;
                    $resourceIdStats[$cannotImportFromBarcode][] = $resource->getId();
                } else {
                    $output->writeln(sprintf("\nLoading barcode %s ...", $barcode));
                    FirewallMiddleware::bypass(
                        function () use (
                            $input,
                            $output,
                            $resourceXml,
                            $barcode,
                            $importConfig,
                            $resource,
                            $imported,
                            $otherErrorDuringImport,
                            &$stats
                        ) {
                            try {
                                $extractedValues = $this->handleCommand(new MarcxmlExtractQuery($resourceXml, $barcode));
                                $importedValues = $this->handleCommand(new MetadataImportQuery($extractedValues, $importConfig));
                                $this->updateResource($resource, $importedValues);
                                $stats[$imported]++;
                            } catch (\Exception $e) {
                                $resourceIdStats[$otherErrorDuringImport][] = $resource->getId();
                            }
                        }
                    );
                    $output->writeln(sprintf("Finished loading barcode %s ", $barcode));
                }
            } else {
                $stats[$error]++;
                $resourceIdStats[$noBarcode][] = $resource->getId();
            }
            $progressBar->advance();
        }
        $progressBar->clear();
        (new Table($output))
            ->setHeaders([$imported, $noBarcode, $cannotImportFromBarcode])
            ->addRows([$stats])
            ->render();
        $output->writeln(sprintf("Resource ids not containing barcode: %s", implode(', ', $resourceIdStats[$noBarcode])));
        $output->writeln(
            sprintf("Resource ids with not downloadable barcodes: %s", implode(', ', $resourceIdStats[$cannotImportFromBarcode]))
        );
    }

    public function updateResource(ResourceEntity $resource, $importedValues) {
        $newContents = $resource->getContents();
        foreach ($importedValues->getAcceptedValues() as $metadataId => $valuesFromKoha) {
            $newContents = $newContents->withMergedValues($metadataId, $valuesFromKoha)->clearDuplicates($metadataId);
        }
        if ($resource->getContents()->toArray() != $newContents->toArray()) {
            $resourceUpdateCommand = ResourceGodUpdateCommand::builder()
                ->setResource($resource)
                ->setNewContents($newContents);
            $this->handleCommand($resourceUpdateCommand->build());
        }
    }
}
