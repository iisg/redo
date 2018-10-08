<?php
namespace Repeka\Application\Command\PkImport;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
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
            ->setName('repeka:pk-import:import')
            ->addArgument('input', InputArgument::REQUIRED)
            ->addArgument('config', InputArgument::OPTIONAL)
            ->addOption('resourceKindId', null, InputOption::VALUE_REQUIRED)
            ->addOption('no-report', null, InputOption::VALUE_NONE)
            ->addOption('id-namespace', null, InputOption::VALUE_OPTIONAL)
            ->addOption('unmap-updated', null, InputOption::VALUE_NONE)
            ->setDescription('Imports resources from given file.');
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $stats = [
            'resources' => 0,
            'imported' => 0,
            'updated' => 0,
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
            $resourceKind = $this->resourceKindRepository->findOne($input->getOption('resourceKindId'));
            $importConfig = $this->importConfigFactory->fromFile($configFileName, $resourceKind);
            $reportCreator->setInvalidMetadataKeysInfo($importConfig->getInvalidMetadataKeys());
            $resources = $xml->xpath('/*/*');
            $idMappingNamespace = $input->getOption('id-namespace');
            if (!$idMappingNamespace) {
                $idMappingNamespace = $resources[0]->getName();
            }
            if (!isset($idMapping[$idMappingNamespace])) {
                $idMapping[$idMappingNamespace] = [];
            }
            $output->writeln('Importing resources');
            $progress = new ProgressBar($output, count($resources));
            $progress->display();
            $stats['resources'] = count($resources);
            foreach ($resources as $resource) {
                $progress->advance();
                $resourceData = current($resource->attributes());
                $metadataList = $resource->metadata;
                $terms = [];
                foreach ($metadataList as $metadata) {
                    $termId = (string)$metadata['TERM_ID'];
                    $terms[] = $termId;
                    $metadataData = [];
                    foreach (current($metadata->attributes()) as $attr => $value) {
                        $metadataData[$attr] = $value;
                    }
                    $resourceData[$termId][] = $metadataData;
                }
                FirewallMiddleware::bypass(
                    function () use (
                        $input,
                        $idMappingNamespace,
                        $reportCreator,
                        $resourceKind,
                        $importConfig,
                        $resourceData,
                        &$mappedResourceIds,
                        &$idMapping,
                        &$stats,
                        $terms
                    ) {
                        $importedResult = $this
                            ->handleCommand(new MetadataImportQuery($resourceData, $importConfig));
                        /** @var ResourceContents $importedValues */
                        $importedValues = $importedResult->getAcceptedValues();
                        $resourceId = intval(trim($resourceData['ID']));
                        if (isset($idMapping[$idMappingNamespace][$resourceId])) {
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
                        $this->updateResource($resource, $importedValues, $resourceKind);
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
            ->setHeaders(['Total resources', 'Successfully imported', 'Successfully updated'])
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

    public function updateResource(ResourceEntity $resource, $importedValues, ResourceKind $resourceKind) {
        $newContents = $resource->getContents();
        foreach ($importedValues as $metadataId => $values) {
            $newContents = $newContents->withReplacedValues($metadataId, $values);
        }
        $resourceUpdateCommand = ResourceGodUpdateCommand::builder()
            ->setResource($resource)
            ->setNewContents($newContents)
            ->changeResourceKind($resourceKind)
            ->build();
        $this->handleCommand($resourceUpdateCommand);
    }
}
