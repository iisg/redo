<?php
namespace Repeka\Application\Command;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluateDisplayStrategiesCommand extends Command {
    use CommandBusAware;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('repeka:evaluate-display-strategies')
            ->setDescription('Updates every display strategy metadata in every resource.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        FirewallMiddleware::bypass(
            function () use ($output) {
                $resources = $this->resourceRepository->findAll();
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
}
