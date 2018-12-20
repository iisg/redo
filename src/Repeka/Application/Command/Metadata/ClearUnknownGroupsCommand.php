<?php
namespace Repeka\Application\Command\Metadata;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
class ClearUnknownGroupsCommand extends ContainerAwareCommand {

    private $metadataRepository;
    private $commandBus;

    public function __construct(
        MetadataRepository $metadataRepository,
        CommandBus $commandBus
    ) {
        parent::__construct();
        $this->metadataRepository = $metadataRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure() {
        $this
            ->setName('repeka:metadata:clear-unknown-groups')
            ->setDescription('Sets default group for metadata with unknown/deleted group ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $allKnown = true;
        $groupIds = array_column($this->getContainer()->getParameter('repeka.metadata_groups'), 'id');
        $output->write(PHP_EOL);
        foreach ($this->metadataRepository->findAll() as $metadata) {
            $groupId = $metadata->getGroupId();
            if ($groupId != Metadata::DEFAULT_GROUP && !in_array($groupId, $groupIds)) {
                $this->setDefaultGroupId($metadata);
                $output->writeln("-> found unknown group id '" . $groupId . "' in " . $metadata->getName());
                $allKnown = false;
            }
        }
        if ($allKnown) {
            $output->writeln("All clear. Nothing to do." . PHP_EOL);
        } else {
            $output->writeln(PHP_EOL . "All encountered invalid ids were changed into " . Metadata::DEFAULT_GROUP . PHP_EOL);
        }
    }

    private function setDefaultGroupId(Metadata $metadata) {
        FirewallMiddleware::bypass(
            function () use ($metadata) {
                $this->commandBus->handle(
                    new MetadataUpdateCommand(
                        $metadata,
                        $metadata->getLabel(),
                        $metadata->getDescription(),
                        $metadata->getPlaceholder(),
                        $metadata->getConstraints(),
                        Metadata::DEFAULT_GROUP,
                        $metadata->isShownInBrief(),
                        $metadata->isCopiedToChildResource()
                    )
                );
            }
        );
    }
}
