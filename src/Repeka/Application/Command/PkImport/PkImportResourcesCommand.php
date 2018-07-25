<?php
namespace Repeka\Application\Command\PkImport;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class PkImportResourcesCommand extends Command {
    use CommandBusAware;

    const ID_MAPPING_FILE = \AppKernel::VAR_PATH . '/import/id-mapping.json';

    /** @var ImportConfigFactory */
    private $importConfigFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ImportConfigFactory $importConfigFactory, ResourceKindRepository $resourceKindRepository) {
        $this->importConfigFactory = $importConfigFactory;
        $this->resourceKindRepository = $resourceKindRepository;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('repeka:pk-import:import')
            ->addArgument('input', InputArgument::REQUIRED)
            ->addArgument('config', InputArgument::REQUIRED)
            ->addArgument('resourceKindId', InputArgument::REQUIRED)
            ->setDescription('Imports resources from given file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $stats = [
            'resources' => 0,
            'imported' => 0,
            'ignored' => 0,
        ];
        $idMapping = self::getIdMapping();
        try {
            $xml = PkImportFileLoader::load($input->getArgument('input'));
            $resourceKind = $this->resourceKindRepository->findOne($input->getArgument('resourceKindId'));
            $importConfig = $this->importConfigFactory->fromFile($input->getArgument('config'), $resourceKind);
            $resources = $xml->xpath('/*/*');
            $output->writeln('Importing resources');
            $progress = new ProgressBar($output, count($resources));
            $progress->display();
            $stats['resources'] = count($resources);
            foreach ($resources as $resource) {
                $progress->advance();
                $resourceData = current($resource->attributes());
                $metadataList = $resource->metadata;
                foreach ($metadataList as $metadata) {
                    $termId = (string)$metadata['TERM_ID'];
                    foreach (current($metadata->attributes()) as $attr => $value) {
                        $resourceData[$termId . '/' . $attr][] = $value;
                    }
                }
                FirewallMiddleware::bypass(
                    function () use ($resourceKind, $importConfig, $resourceData, &$idMapping, &$stats) {
                        $importedValues = $this
                            ->handleCommand(new MetadataImportQuery($resourceData, $importConfig))
                            ->getAcceptedValues();
                        $resourceId = intval(trim($resourceData['ID']));
                        if (isset($idMapping[$resourceId])) {
                            ++$stats['ignored'];
                        } else {
                            $resourceCreateCommand = new ResourceCreateCommand($resourceKind, $importedValues);
                            $resource = $this->handleCommand($resourceCreateCommand);
                            $idMapping[$resourceId] = $resource->getId();
                            ++$stats['imported'];
                        }
                    }
                );
            }
            $progress->clear();
        } catch (\Exception $e) {
            if (isset($progress)) {
                $progress->clear();
            }
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        file_put_contents(self::ID_MAPPING_FILE, json_encode($idMapping));
        (new Table($output))
            ->setHeaders(['Total resources', 'Successfully imported', 'Ignored (already imported)'])
            ->addRows([$stats])
            ->render();
        $output->writeln(
            "Identifiers of the imported resoruces has been saved to:\n<info>" . realpath(self::ID_MAPPING_FILE) . "</info>\n" .
            "Keep this file untouched if you want to repeat the import process or map relations in the future."
        );
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
}
