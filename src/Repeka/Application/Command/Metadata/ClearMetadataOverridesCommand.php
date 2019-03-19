<?php
namespace Repeka\Application\Command\Metadata;

use Assert\Assertion;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Can clear overrides of specific configuration from all resource kinds. Results in treating the metadata settings as the most important
 * one until the next override in resource kind form.
 *
 * @example php bin\console repeka:metadata:clear-overrides plik_zasobu -c fileUploaderType
 */
class ClearMetadataOverridesCommand extends ContainerAwareCommand {

    private $metadataRepository;
    private $commandBus;

    public function __construct(MetadataRepository $metadataRepository, CommandBus $commandBus) {
        parent::__construct();
        $this->metadataRepository = $metadataRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure() {
        $this
            ->setName('repeka:metadata:clear-overrides')
            ->setDescription('Clear specified overrides for given metadeata.')
            ->addArgument('metadata', InputArgument::REQUIRED)
            ->addOption('constraint', 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $metadata = $this->metadataRepository->findByNameOrId($input->getArgument('metadata'));
        $constraints = $input->getOption('constraint') ?: [];
        Assertion::notEmpty($constraints, 'Tell me what constraint overrides to clear.');
        FirewallMiddleware::bypass(
            function () use ($output, $constraints, $metadata) {
                $this->clearMetadataOverrides($metadata, $constraints, $output);
            }
        );
    }

    private function clearMetadataOverrides(Metadata $metadata, array $constraintsToClear, OutputInterface $output) {
        /** @var ResourceKind[] $resourceKinds */
        $resourceKinds = $this->commandBus->handle(ResourceKindListQuery::builder()->filterByMetadataId($metadata->getId())->build());
        $constraintsToClear = array_combine($constraintsToClear, $constraintsToClear);
        foreach ($resourceKinds as $resourceKind) {
            $rkMetadata = $resourceKind->getMetadataById($metadata->getId());
            $overrides = $rkMetadata->getOverrides();
            $clearedOverrides = array_diff_key($overrides, $constraintsToClear);
            if (is_array($overrides['constraints'] ?? null)) {
                $clearedOverrides['constraints'] = array_diff_key($overrides['constraints'], $constraintsToClear);
            }
            if ($overrides != $clearedOverrides) {
                $newMetadataList = array_map(
                    function (Metadata $rkMetadata) use ($clearedOverrides, $metadata) {
                        if ($rkMetadata->getId() == $metadata->getId()) {
                            return $rkMetadata->withOverrides($clearedOverrides);
                        } else {
                            return $rkMetadata;
                        }
                    },
                    $resourceKind->getMetadataList()
                );
                $this->commandBus->handle(
                    new ResourceKindUpdateCommand($resourceKind, $resourceKind->getLabel(), $newMetadataList, $resourceKind->getWorkflow())
                );
                $output->writeln('Cleared metadata overrides in resource kind ' . $resourceKind->getName());
            }
        }
    }
}
