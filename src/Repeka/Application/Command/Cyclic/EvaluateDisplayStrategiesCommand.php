<?php
namespace Repeka\Application\Command\Cyclic;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluateDisplayStrategiesCommand extends Command implements CyclicCommand {
    use CommandBusAware;

    /** @var ResourceRepository|EntityRepository */
    private $resourceRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(ResourceRepository $resourceRepository, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('repeka:evaluate-display-strategies')
            ->setDescription('Updates every display strategy metadata in every resource.')
            ->addOption('batch', 'b', InputOption::VALUE_REQUIRED, null, 100)
            ->addOption('all', 'a', InputOption::VALUE_NONE)
            ->addOption('set-dirty', 'd', InputOption::VALUE_NONE)
            ->addOption('resourceIds', 'r', InputOption::VALUE_REQUIRED);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($input->getOption('set-dirty')) {
            $condition = $input->getOption('resourceIds') ?: '1=1';
            $query = 'UPDATE resource SET display_strategies_dirty = true WHERE ' . $condition;
            $affected = $this->entityManager->getConnection()->exec($query);
            $output->writeln("<comment>$affected resources set dirty.</comment>");
            return;
        }
        ini_set('memory_limit', '768M');
        if ($input->getOption('all')) {
            $resources = $this->resourceRepository->findAll();
        } elseif ($resourceIds = $input->getOption('resourceIds')) {
            $resourceIds = explode(',', $resourceIds);
            $resources = $this->resourceRepository->findByQuery(ResourceListQuery::builder()->filterByIds($resourceIds)->build());
        } else {
            $limit = $input->getOption('batch');
            $resources = $this->resourceRepository->findBy(['displayStrategiesDirty' => true], null, $limit);
            if (empty($resources)) {
                $output->writeln('No dirty resources found.');
                return;
            }
        }
        FirewallMiddleware::bypass(
            function () use ($resources, $output) {
                $progressBar = new ProgressBar($output, count($resources));
                $output->writeln('Evaluating display strategies...');
                $progressBar->start();
                $changed = 0;
                foreach ($resources as $resource) {
                    $contentsBefore = $resource->getContents();
                    $updated = $this->handleCommand(new ResourceEvaluateDisplayStrategiesCommand($resource));
                    if ($updated->getContents() != $contentsBefore) {
                        $changed++;
                    }
                    $progressBar->advance();
                }
                $progressBar->clear();
                $output->writeln('Changed resources: ' . $changed);
            }
        );
    }

    public function getIntervalInMinutes(): int {
        return 1;
    }
}
