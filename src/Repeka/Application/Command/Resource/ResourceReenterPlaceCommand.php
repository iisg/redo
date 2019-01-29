<?php
namespace Repeka\Application\Command\Resource;

use Assert\Assertion;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceReenterPlaceCommand extends Command {
    use Transactional;

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(ResourceRepository $resourceRepository, CommandBus $commandBus) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure() {
        $this
            ->setName('repeka:resources:reenter-place')
            ->setDescription('Forces resources to reenter their place, causing all configured plugins to be executed again.')
            ->addOption('parentId', null, InputOption::VALUE_REQUIRED)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $parentId = $input->getOption('parentId');
        Assertion::numeric($parentId);
        /** @var ResourceEntity[] $resources */
        $query = ResourceListQuery::builder()->filterByParentId($parentId)->build();
        $resources = $this->resourceRepository->findByQuery($query);
        $progress = new ProgressBar($output, count($resources));
        $progress->display();
        $offset = $input->getOption('offset') ?? 0;
        $limit = $offset + ($input->getOption('limit') ?? count($resources));
        $iteration = 0;
        foreach ($resources as $resource) {
            $progress->advance();
            $iteration++;
            if ($iteration < $offset) {
                continue;
            } elseif ($iteration > $limit) {
                break;
            }
            if ($resource->hasWorkflow()) {
                FirewallMiddleware::bypass(
                    function () use ($resource) {
                        $transition = SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource);
                        $this->commandBus->handle(
                            new ResourceTransitionCommand($resource, $resource->getContents(), $transition)
                        );
                    }
                );
            }
        }
        $progress->finish();
    }
}
