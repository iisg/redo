<?php
namespace Repeka\Application\Command\Cyclic;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluateDisplayStrategiesCommand extends Command implements CyclicCommand {
    use CommandBusAware;

    /** @var ResourceRepository|EntityRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:evaluate-display-strategies')
            ->setDescription('Updates every display strategy metadata in every resource.')
            ->addOption('batch', 'b', InputOption::VALUE_REQUIRED, null, 100)
            ->addOption('all', 'a', InputOption::VALUE_NONE);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '768M');
        if ($input->getOption('all')) {
            $resources = $this->resourceRepository->findAll();
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
                $output->writeln('Evaluating all display strategies...');
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
