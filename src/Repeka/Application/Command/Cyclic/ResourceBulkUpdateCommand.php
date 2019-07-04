<?php
namespace Repeka\Application\Command\Cyclic;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Factory\Audit;
use Repeka\Domain\Factory\BulkChanges\BulkChangeFactory;
use Repeka\Domain\Factory\BulkChanges\PendingUpdates;
use Repeka\Domain\Repository\ResourceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceBulkUpdateCommand extends Command implements CyclicCommand {
    use Transactional, CommandBusAware;

    /** @var ResourceRepository|EntityRepository $resourceRepository */
    private $resourceRepository;
    /** @var BulkChangeFactory $bulkChangeFactory */
    private $bulkChangeFactory;
    /** @var Audit $audit */
    private $audit;

    private const AUDIT_NAME = 'resources_bulk_update';
    private const DEFAULT_LIMIT = 30;

    public function __construct(
        ResourceRepository $resourceRepository,
        BulkChangeFactory $bulkChangeFactory,
        Audit $audit
    ) {
        parent::__construct();
        $this->resourceRepository = $resourceRepository;
        $this->bulkChangeFactory = $bulkChangeFactory;
        $this->audit = $audit;
    }

    public function configure() {
        $this->setName('repeka:resources-bulk-update')
            ->setDescription('Executes pending updates in resources')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Number of resources updated at once', self::DEFAULT_LIMIT);
    }

    /** @inheritdoc */
    public function execute(InputInterface $input, OutputInterface $output) {
        $resourcesToUpdate = $this->resourceRepository->getResourcesWithPendingUpdates($input->getOption('limit'));
        if ($resourcesToUpdate) {
            $this->transactional(
                function () use ($resourcesToUpdate, $output) {
                    $this->bulkUpdate($resourcesToUpdate, $output);
                }
            );
        }
    }

    /** @param ResourceEntity[] $resourcesToUpdate */
    private function bulkUpdate(array $resourcesToUpdate, OutputInterface $output) {
        $progressBar = new ProgressBar($output, count($resourcesToUpdate));
        $output->writeln('Updating resources...');
        $progressBar->start();
        $changed = 0;
        foreach ($resourcesToUpdate as $resource) {
            $updates = $resource->getPendingUpdates();
            while ($update = $updates->shiftUpdate()) {
                try {
                    $change = $this->bulkChangeFactory->create($update);
                    $resource = $change->apply($resource);
                } catch (\Exception $e) {
                    $auditData = [
                        'resourceId' => $resource->getId(),
                        'update' => $update,
                        'errorMessage' => $e->getMessage(),
                    ];
                    $this->audit->newEntry(self::AUDIT_NAME, null, $auditData, false);
                }
            }
            $resource->setPendingUpdates(PendingUpdates::empty());
            $this->resourceRepository->save($resource);
            $changed++;
            $progressBar->advance();
        }
        $progressBar->clear();
        $output->writeln('Changed resources: ' . $changed);
    }

    public function getIntervalInMinutes(): int {
        return 1;
    }
}
