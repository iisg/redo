<?php
namespace Repeka\Plugins\Redo\Command\Import;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\DispatchCommandEventsMiddleware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\EventListener\UpdateDependentDisplayStrategiesListener;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings("PHPMD.NPathComplexity")
 */
class PkImportMapRelationsCommand extends Command {
    use CommandBusAware;

    const MAPPED_RESOURCES_FILE = \AppKernel::VAR_PATH . '/import/mapped-relations.json';

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceRepository $resourceRepository, MetadataRepository $metadataRepository) {
        $this->resourceRepository = $resourceRepository;
        $this->metadataRepository = $metadataRepository;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('redo:pk-import:map-relations')
            ->setDescription('Map relations of imported files.')
            ->addOption('idNamespace', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('skip', 's', InputOption::VALUE_REQUIRED)
            ->addOption('ignoreProblems', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('fixProblems', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('customMap', 'c', InputOption::VALUE_REQUIRED);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '768M');
        DispatchCommandEventsMiddleware::$dispatchEvents = false;
        UpdateDependentDisplayStrategiesListener::$alwaysLeaveDirty = true;
        $idMapping = PkImportResourcesCommand::getIdMapping();
        $idNamespaces = $this->loadIdNamespaces($input);
        $customMaps = $this->loadCustomMaps($input);
        $fixProblems = $this->loadFixProblems($input);
        $problemsLog = [];
        if (!$idMapping) {
            $output->writeln('<error>You need to import some resources first.');
        } else {
            $mappedResourceIds = self::getAlreadyMappedResourceIds();
            $importedResourceIds = ArrayUtils::flatten($idMapping);
            $idsToQuery = array_diff($importedResourceIds, $mappedResourceIds);
            $stats = [
                'total' => 0,
                'mapped' => 0,
                'no_need' => 0,
                'insufficient' => 0,
            ];
            $metadataWithoutNamespace = [];
            $missingMetadataIds = [];
            $missingMetadataValues = [];
            if (count($idsToQuery)) {
                $query = ResourceListQuery::builder()->filterByIds($idsToQuery)->setResultsPerPage(2000)->build();
                $resources = $this->resourceRepository->findByQuery($query);
                $progress = new ProgressBar($output, count($resources));
                $progress->display();
                $stats['total'] = count($resources);
                $relationshipMetadata = $this->metadataRepository->findByQuery(
                    MetadataListQuery::builder()->filterByControl(MetadataControl::RELATIONSHIP())->build()
                );
                $relationshipMetadataIds = EntityUtils::mapToIds($relationshipMetadata);
                $skippedMetadataIds = EntityUtils::mapToIds(
                    array_map([$this, 'findMetadataByIdOrName'], array_filter(explode(',', $input->getOption('skip'))))
                );
                $ignoreProblemsInMetadataIds = array_filter(explode(',', $input->getOption('ignoreProblems')));
                $relationshipMetadataIds = array_diff($relationshipMetadataIds, $skippedMetadataIds);
                try {
                    foreach ($resources as $resource) {
                        // echo PHP_EOL . $resource->getId() . PHP_EOL;
                        $progress->advance();
                        $mappedEverything = true;
                        $mappedContents = $resource->getContents()->mapAllValues(
                            function (
                                MetadataValue $value,
                                int $metadataId
                            ) use (
                                $customMaps,
                                $idMapping,
                                $idNamespaces,
                                $resource,
                                $relationshipMetadataIds,
                                $ignoreProblemsInMetadataIds,
                                $fixProblems,
                                &$metadataWithoutNamespace,
                                &$missingMetadataIds,
                                &$missingMetadataValues,
                                &$mappedEverything,
                                &$problemsLog
                            ) {
                                if (in_array($metadataId, $relationshipMetadataIds)) {
                                    $namespace = $idNamespaces[$metadataId] ?? null;
                                    if ($metadataId == SystemMetadata::PARENT) {
                                        $namespace = $resource->getResourceClass() == 'dictionaries' ? 'indexItem' : 'resource';
                                    }
                                    if (!$namespace) {
                                        $metadataWithoutNamespace[] = $metadataId;
                                    } else {
                                        if (isset($idMapping[$namespace][$value->getValue()])) {
                                            return $value->withNewValue($idMapping[$namespace][$value->getValue()]);
                                        } elseif ($namespace == 'resource' && isset($customMaps[$value->getValue()])) {
                                            return $value->withNewValue($customMaps[$value->getValue()]);
                                        } else {
                                            $missingMetadataIds[] = $metadataId;
                                            $missingMetadataValues[$metadataId][] = $value->getValue();
                                            $problemsLog[] = [
                                                $resource->getId(), // redo id
                                                $metadataId, // metadata id
                                                $resource->getContents()->getValuesWithoutSubmetadata(192)[0]
                                                ?? $resource->getContents()->getValuesWithoutSubmetadata(197)[0]
                                                ?? 'xx', // suwid
                                                $value->getValue(), // value
                                            ];
                                            if (isset($fixProblems[$metadataId])) {
                                                return $value->withNewValue($fixProblems[$metadataId]);
                                            }
                                            if (in_array($metadataId, $ignoreProblemsInMetadataIds)) {
                                                return null;
                                            }
                                        }
                                    }
                                    $mappedEverything = false;
                                }
                                return $value;
                            }
                        );
                        if ($mappedEverything) {
                            if ($mappedContents != $resource->getContents()) {
                                FirewallMiddleware::bypass(
                                    function () use ($mappedContents, $resource) {
                                        $command = ResourceGodUpdateCommand::builder()
                                            ->setResource($resource)
                                            ->setNewContents($mappedContents)
                                            ->build();
                                        $this->handleCommand($command);
                                    }
                                );
                                ++$stats['mapped'];
                            } else {
                                ++$stats['no_need'];
                            }
                            $mappedResourceIds[] = $resource->getId();
                        } else {
                            ++$stats['insufficient'];
                        }
                    }
                    $progress->clear();
                } catch (\Exception $e) {
                    $progress->clear();
                    throw $e;
                    // $output->writeln('<error>' . $e->getMessage() . '</error>');
                }
            }
            file_put_contents(self::MAPPED_RESOURCES_FILE, json_encode(array_values($mappedResourceIds)));
            (new Table($output))
                ->setHeaders(
                    ['Total resources', 'Successfully mapped', 'Ignored (no relationship metadata)', 'Ignored (missing relationships)']
                )
                ->addRows([$stats])
                ->render();
            if ($metadataWithoutNamespace) {
                $output->writeln(
                    '<error>The following metadata have no namespace specified:</error> '
                    . implode(', ', array_unique($metadataWithoutNamespace))
                );
            }
            if ($missingMetadataIds) {
                $output->writeln(
                    '<error>The following metadata have been missing (referenced resources not found in the imported data):</error> '
                    . implode(', ', array_unique($missingMetadataIds))
                );
            }
            $missingMetadataValues = array_map('array_values', array_map('array_unique', $missingMetadataValues));
            $missingMetadataValues = array_map(
                function ($metadataId, $missing) {
                    return $metadataId . ': ' . implode(', ', $missing);
                },
                array_keys($missingMetadataValues),
                $missingMetadataValues
            );
            $output->writeln(implode(PHP_EOL, $missingMetadataValues));
            if ($problemsLog) {
                $path = \AppKernel::VAR_PATH . '/import/relations-mapping-problems-' . date('Ymd-His') . '.csv';
                $contents = 'REDO ID,METADATA ID,SUW ID,NIEZNALEZIONY ID' . PHP_EOL;
                $contents .= implode(
                    PHP_EOL,
                    array_map(
                        function ($a) {
                            return implode(',', $a);
                        },
                        $problemsLog
                    )
                );
                file_put_contents($path, $contents);
                $output->writeln('Problems log has been saved to ' . realpath($path));
            }
        }
    }

    public static function getAlreadyMappedResourceIds(): array {
        return (file_exists(self::MAPPED_RESOURCES_FILE) ? json_decode(file_get_contents(self::MAPPED_RESOURCES_FILE), true) : []) ?: [];
    }

    private function loadIdNamespaces(InputInterface $input): array {
        $options = $input->getOption('idNamespace');
        $idNamespaces = [];
        foreach ($options as $option) {
            $namespace = explode(':', $option)[0];
            $metadataList = array_map([$this, 'findMetadataByIdOrName'], explode(',', explode(':', $option)[1]));
            foreach ($metadataList as $metadata) {
                $idNamespaces[$metadata->getId()] = $namespace;
            }
        }
        return $idNamespaces;
    }

    private function loadCustomMaps(InputInterface $input): array {
        $pairs = array_values(array_filter(explode(',', $input->getOption('customMap') ?? '')));
        $map = [];
        foreach ($pairs as $pair) {
            list($suwResourceId, $redoResourceId) = explode(':', $pair);
            $map[intval($suwResourceId)] = intval($redoResourceId);
        }
        return $map;
    }

    public function findMetadataByIdOrName($metadataIdOrName): Metadata {
        try {
            return $this->metadataRepository->findByNameOrId($metadataIdOrName);
        } catch (EntityNotFoundException $e) {
            throw new \RuntimeException('Unknown metadata: ' . $metadataIdOrName, 0, $e);
        }
    }

    private function loadFixProblems(InputInterface $input) {
        $pairs = array_values(array_filter(explode(',', $input->getOption('fixProblems') ?? '')));
        $map = [];
        foreach ($pairs as $pair) {
            list($suwResourceId, $redoResourceId) = explode(':', $pair);
            $map[intval($suwResourceId)] = intval($redoResourceId);
        }
        return $map;
    }
}
