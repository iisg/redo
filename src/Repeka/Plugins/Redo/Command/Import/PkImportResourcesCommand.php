<?php
namespace Repeka\Plugins\Redo\Command\Import;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\DispatchCommandEventsMiddleware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\EventListener\UpdateDependentDisplayStrategiesListener;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfig;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Plugins\Redo\Command\Import\XmlExtractStrategy\PkImportXmlExtractor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.NPathComplexity")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class PkImportResourcesCommand extends ContainerAwareCommand {
    use CommandBusAware;

    const ID_MAPPING_FILE = \AppKernel::VAR_PATH . '/import/id-mapping.json';

    const IMPORTED = 'imported';
    const UPDATED = 'updated';

    /** @var ImportConfigFactory */
    private $importConfigFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(
        ImportConfigFactory $importConfigFactory,
        ResourceKindRepository $resourceKindRepository,
        ResourceRepository $resourceRepository
    ) {
        $this->importConfigFactory = $importConfigFactory;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('redo:pk-import:import')
            ->addArgument('input', InputArgument::REQUIRED)
            ->addArgument('config', InputArgument::OPTIONAL)
            ->addOption('resourceKindId', null, InputOption::VALUE_REQUIRED)
            ->addOption('resourceKindIdMap', null, InputOption::VALUE_REQUIRED)
            ->addOption('skipExisting', null, InputOption::VALUE_NONE)
            ->addOption('exportFormat', null, InputOption::VALUE_REQUIRED)
            ->addOption('no-report', null, InputOption::VALUE_NONE)
            ->addOption('id-namespace', null, InputOption::VALUE_OPTIONAL)
            ->addOption('unmap-updated', null, InputOption::VALUE_NONE)
            ->addOption('delete-hidden', null, InputOption::VALUE_NONE)
            ->addOption('workflow-place', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->setDescription('Imports resources from given file.');
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '768M');
        date_default_timezone_set('Europe/Warsaw');
        UpdateDependentDisplayStrategiesListener::$alwaysLeaveDirty = true;
        DispatchCommandEventsMiddleware::$dispatchEvents = false;
        $stats = [
            'resources' => 0,
            'imported' => 0,
            'updated' => 0,
            'skippedHidden' => 0,
            'skippedExisting' => 0,
        ];
        $idMapping = self::getIdMapping();
        $mappedResourceIds = PkImportMapRelationsCommand::getAlreadyMappedResourceIds();
        $error = null;
        $xmlFileName = $input->getArgument('input');
        $configFileName = $input->getArgument('config');
        if (!$configFileName) {
            $configFileName = substr($xmlFileName, 0, -4) . '.yml';
            if (!file_exists($configFileName)) {
                $configFileName = substr($xmlFileName, 0, -4) . '.json';
            }
        }
        $applicationUrl = $this->getContainer()->getParameter('repeka.application_url');
        $reportCreator = new PkImportHtmlReport($applicationUrl, $xmlFileName);
        try {
            $xml = PkImportFileLoader::load($xmlFileName);
            $skipExisting = $input->getOption('skipExisting');
            $resourceKindNameOrId = $input->getOption('resourceKindId');
            $resourceKindsMap = $resourceKindNameOrId ? '-1:' . $resourceKindNameOrId : $input->getOption('resourceKindIdMap');
            $resourceKindsMap = $this->buildResourceKindsMap($resourceKindsMap);
            $workflowPlacesIds = []; //$this->findWorkflowPlacesIds($resourceKind, $input->getOption('workflow-place'));
            $importConfigs = $this->loadImportConfigs($resourceKindsMap, $configFileName);
            $resourceContentsFetcherName = 'Repeka\\Plugins\\Redo\\Command\\Import\\XmlExtractStrategy\\';
            $resourceContentsFetcherName .= $input->getOption('exportFormat') . 'XmlExtractor';
            /** @var PkImportXmlExtractor $resourceContentsFetcher */
            $resourceContentsFetcher = new $resourceContentsFetcherName();
            $resources = $resourceContentsFetcher->extractAllResources($xml);
            $idMappingNamespace = $input->getOption('id-namespace');
            if (!$idMappingNamespace) {
                $idMappingNamespace = $resources[0]->getName();
            }
            if (!isset($idMapping[$idMappingNamespace])) {
                $idMapping[$idMappingNamespace] = [];
            }
            $progress = new ProgressBar($output, count($resources));
            $stats['resources'] = count($resources);
            $offset = $input->getOption('offset') ?? 0;
            $limit = $offset + ($input->getOption('limit') ?? $stats['resources']);
            $iteration = -1;
            if ($offset > count($resources)) {
                $output->writeln('<comment>No more resources.</comment>');
                return 1;
            }
            $output->writeln("Importing resources $offset..$limit");
            $progress->display();
            foreach ($resources as $resource) {
                $progress->advance();
                $iteration++;
                if ($iteration < $offset) {
                    continue;
                } elseif ($iteration >= $limit) {
                    break;
                }
                $resourceData = $resourceContentsFetcher->extractResourceData($resource);
                Assertion::keyExists($resourceData, 'ID');
                if ($resourceKindNameOrId) {
                    $resourceData['KIND_ID'] = '-1';
                }
                Assertion::keyExists($resourceData, 'KIND_ID');
                if (is_array($resourceData['ID'])) {
                    $resourceData['ID'] = current($resourceData['ID']);
                }
                if (isset($resourceData['VISIBLE']) && !$resourceData['VISIBLE']) {
                    $stats['skippedHidden']++;
                    $resourceId = intval(trim($resourceData['ID']));
                    $existingResourceId = $idMapping[$idMappingNamespace][$resourceId] ?? null;
                    $status = 'INGNORED_HIDDEN';
                    if ($existingResourceId && $input->getOption('delete-hidden')) {
                        $resource = $this->resourceRepository->findOne($idMapping[$idMappingNamespace][$resourceId]);
                        $this->resourceRepository->delete($resource);
                        $status = 'DELETED_HIDDEN';
                        unset($idMapping[$idMappingNamespace][$resourceId]);
                    }
                    $reportCreator->addResourceImportStatus($resourceData['ID'], $existingResourceId, $status, [], []);
                    continue;
                }
                Assertion::keyExists($resourceKindsMap, $resourceData['KIND_ID']);
                $resourceKind = $resourceKindsMap[$resourceData['KIND_ID']];
                $importConfig = $importConfigs[$resourceKind->getId()];
                $terms = array_keys($resourceData);
                FirewallMiddleware::bypass(
                    function () use (
                        $input,
                        $idMappingNamespace,
                        $reportCreator,
                        $resourceKind,
                        $workflowPlacesIds,
                        $importConfig,
                        $resourceData,
                        &$mappedResourceIds,
                        &$idMapping,
                        &$stats,
                        $terms,
                        $skipExisting
                    ) {
                        $importedResult = $this
                            ->handleCommand(new MetadataImportQuery($resourceData, $importConfig));
                        /** @var ResourceContents $importedValues */
                        $importedValues = $importedResult->getAcceptedValues();
                        $resourceId = intval(trim($resourceData['ID']));
                        if (isset($idMapping[$idMappingNamespace][$resourceId])) {
                            if ($skipExisting) {
                                $stats['skippedExisting']++;
                                $reportCreator->addResourceImportStatus(
                                    $resourceData['ID'],
                                    $idMapping[$idMappingNamespace][$resourceId],
                                    'INGNORED_EXISTING',
                                    [],
                                    []
                                );
                                return;
                            }
                            $resource = $this->resourceRepository->findOne($idMapping[$idMappingNamespace][$resourceId]);
                            ++$stats[PkImportResourcesCommand::UPDATED];
                            $status = PkImportResourcesCommand::UPDATED;
                            if ($input->getOption('unmap-updated')) {
                                $mappedResourceIds = array_diff($mappedResourceIds, [$resource->getId()]);
                            }
                        } else {
                            $resourceCreateCommand = new ResourceCreateCommand($resourceKind, ResourceContents::empty());
                            $resource = $this->handleCommand($resourceCreateCommand);
                            $idMapping[$idMappingNamespace][$resourceId] = $resource->getId();
                            ++$stats[PkImportResourcesCommand::IMPORTED];
                            $status = PkImportResourcesCommand::IMPORTED;
                        }
                        $this->updateResource($resource, $importedValues, $resourceKind, $workflowPlacesIds);
                        $unfitTypeValues = $importedResult->getUnfitTypeValues();
                        $notUsedTerms = $this->getNotUsedTerms($terms, $importConfig);
                        $reportCreator->addResourceImportStatus(
                            $resourceId,
                            $resource->getId(),
                            $status,
                            $unfitTypeValues,
                            $notUsedTerms
                        );
                    }
                );
            }
            $progress->clear();
        } catch (\Exception $e) {
            if (isset($progress)) {
                $progress->clear();
            }
            $error = $e->getMessage();
            $reportCreator->setError($error);
        }
        file_put_contents(self::ID_MAPPING_FILE, json_encode($idMapping));
        if ($input->getOption('unmap-updated')) {
            file_put_contents(PkImportMapRelationsCommand::MAPPED_RESOURCES_FILE, json_encode($mappedResourceIds));
        }
        (new Table($output))
            ->setHeaders(
                ['Total resources', 'Successfully imported', 'Successfully updated', 'Skipped hidden', 'Skipped existing']
            )
            ->addRows([$stats])
            ->render();
        $output->writeln(
            "Identifiers of the imported resources has been saved to:\n<info>" . realpath(self::ID_MAPPING_FILE) . "</info>\n" .
            "Keep this file untouched if you want to repeat the import process or map relations in the future."
        );
        if (!$input->getOption('no-report')) {
            $reportCreator->writeReport();
            $path = realpath($reportCreator->getOutputFilename());
            $output->writeln("Import report has been saved to $path");
        }
        if ($error) {
            $output->writeln('<error>IMPORT HAS NOT BEEN FINISHED DUE TO AN ERROR:</error>');
            $output->writeln('<error>' . $error . '</error>');
            return 1;
        }
    }

    public static function getIdMapping(): array {
        if (!file_exists(self::ID_MAPPING_FILE)) {
            if (!@file_put_contents(self::ID_MAPPING_FILE, '{}')) {
                throw new \RuntimeException(
                    'Could not write to the file ' . self::ID_MAPPING_FILE
                    . '. Make sure the directory exists and is writable and try again.'
                );
            }
        }
        return json_decode(file_get_contents(self::ID_MAPPING_FILE), true);
    }

    public function getNotUsedTerms($terms, $importConfig) {
        $importKeys = array_map(
            function ($mapping) {
                return explode('/', $mapping->getImportKey())[0];
            },
            $importConfig->getMappings()
        );
        return array_unique(array_diff($terms, $importKeys));
    }

    public function updateResource(
        ResourceEntity $resource,
        ResourceContents $importedValues,
        ResourceKind $resourceKind,
        array $placesIds = []
    ) {
        $newContents = $resource->getContents();
        $importedValues = $importedValues->filterOutEmptyMetadata();
        foreach ($importedValues as $metadataId => $values) {
            $newContents = $newContents->withReplacedValues($metadataId, $values);
        }
        $resourceUpdateCommand = ResourceGodUpdateCommand::builder()
            ->setResource($resource)
            ->setNewContents($newContents)
            ->changeResourceKind($resourceKind);
        if ($placesIds) {
            $resourceUpdateCommand = $resourceUpdateCommand->changePlaces($placesIds);
        }
        $this->handleCommand($resourceUpdateCommand->build());
    }

    // private function findWorkflowPlacesIds(ResourceKind $resourceKind, array $placesLabelsOrIds): array {
    //     if ($placesLabelsOrIds) {
    //         $places = $resourceKind->getWorkflow()->getPlaces();
    //         $places = array_filter(
    //             $places,
    //             function (ResourceWorkflowPlace $place) use ($placesLabelsOrIds) {
    //                 return in_array($place->getId(), $placesLabelsOrIds) || array_intersect($place->getLabel(), $placesLabelsOrIds);
    //             }
    //         );
    //         Assertion::count($places, count($placesLabelsOrIds), 'Some places were not found.');
    //         return EntityUtils::mapToIds($places);
    //     }
    //     return [];
    // }
    /** @return ResourceKind[] */
    private function buildResourceKindsMap(string $resourceKindsIdMap): array {
        $pairs = explode(',', $resourceKindsIdMap);
        $map = [];
        foreach ($pairs as $pair) {
            list($suwKindId, $redoKindNameOrId) = explode(':', $pair);
            $map[$suwKindId] = $this->resourceKindRepository->findByNameOrId($redoKindNameOrId);
        }
        return $map;
    }

    /** @return ImportConfig[] */
    private function loadImportConfigs(array $resourceKindsMap, string $configFileName): array {
        $configs = [];
        foreach ($resourceKindsMap as $resourceKind) {
            $config = $this->importConfigFactory->fromFile($configFileName, $resourceKind);
            // $invalidMetadataKeys = $config->getInvalidMetadataKeys();
            // Assertion::count($invalidMetadataKeys, 0, 'Invalid metadata keys: ' . implode(', ', $invalidMetadataKeys));
            $configs[$resourceKind->getId()] = $config;
        }
        return $configs;
    }
}
