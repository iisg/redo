<?php
namespace Repeka\Application\Command\PkImport;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @SuppressWarnings("PHPMD.CyclomaticComplexity") */
class PkImportMapRelationsCommand extends Command {
    use CommandBusAware;

    const MAPPED_RESOURCES_FILE = \AppKernel::VAR_PATH . '/import/mapped-relations.json';

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('repeka:pk-import:map-relations')
            ->setDescription('Map relations of imported files.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $idMapping = PkImportResourcesCommand::getIdMapping();
        if (!$idMapping) {
            $output->writeln('<error>You need to import some resources first.');
        } else {
            $mappedResourceIds = self::getAlreadyMappedResourceIds();
            $idsToQuery = array_diff(array_values($idMapping), $mappedResourceIds);
            $stats = [
                'total' => 0,
                'mapped' => 0,
                'no_need' => 0,
                'insufficient' => 0,
            ];
            if (count($idsToQuery)) {
                $query = ResourceListQuery::builder()->filterByIds($idsToQuery)->build();
                $resources = $this->resourceRepository->findByQuery($query);
                $progress = new ProgressBar($output, count($resources));
                $progress->display();
                $stats['total'] = count($resources);
                try {
                    foreach ($resources as $resource) {
                        $progress->advance();
                        $relationshipMetadata = $resource->getKind()->getMetadataByControl(MetadataControl::RELATIONSHIP());
                        $mappedContents = $resource->getContents();
                        $mappedEverything = true;
                        foreach ($relationshipMetadata as $metadata) {
                            $values = $resource->getContents()->getValuesWithoutSubmetadata($metadata);
                            if ($values) {
                                $mappedValues = [];
                                foreach ($values as $value) {
                                    if (isset($idMapping[$value])) {
                                        $mappedValues[] = $idMapping[$value];
                                    } else {
                                        $mappedEverything = false;
                                        ++$stats['insufficient'];
                                        break 2;
                                    }
                                }
                                $mappedContents = $mappedContents->withReplacedValues($metadata, $mappedValues);
                            }
                        }
                        if ($mappedEverything) {
                            if ($mappedContents != $resource->getContents()) {
                                FirewallMiddleware::bypass(
                                    function () use ($mappedContents, $resource) {
                                        $this->handleCommand(new ResourceUpdateContentsCommand($resource, $mappedContents));
                                    }
                                );
                                ++$stats['mapped'];
                            } else {
                                ++$stats['no_need'];
                            }
                            $mappedResourceIds[] = $resource->getId();
                        }
                    }
                    $progress->clear();
                } catch (\Exception $e) {
                    $progress->clear();
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                }
            }
            file_put_contents(self::MAPPED_RESOURCES_FILE, json_encode($mappedResourceIds));
            (new Table($output))
                ->setHeaders(
                    ['Total resources', 'Successfully mapped', 'Ignored (no relationship metadata)', 'Ignored (missing relationships)']
                )
                ->addRows([$stats])
                ->render();
        }
    }

    private static function getAlreadyMappedResourceIds(): array {
        return (file_exists(self::MAPPED_RESOURCES_FILE) ? json_decode(file_get_contents(self::MAPPED_RESOURCES_FILE), true) : []) ?: [];
    }
}
