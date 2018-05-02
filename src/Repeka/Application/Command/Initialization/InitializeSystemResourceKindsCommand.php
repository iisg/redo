<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemResourceKindsCommand extends TransactionalCommand {
    use CommandBusAware;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var EntityIdGeneratorHelper */
    private $idGeneratorHelper;

    public function __construct(EntityIdGeneratorHelper $idGeneratorHelper, ResourceKindRepository $resourceKindRepository) {
        parent::__construct();
        $this->idGeneratorHelper = $idGeneratorHelper;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-resource-kinds')
            ->setDescription('Inserts system resource kinds.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        $this->idGeneratorHelper->preventGeneratingIds(ResourceKind::class);
        $this->createUserResourceKind($output);
        $this->idGeneratorHelper->restoreIdGenerator(ResourceKind::class, 'resource_kind_id_seq');
    }

    private function createUserResourceKind(OutputInterface $output) {
        if (!$this->resourceKindRepository->exists(SystemResourceKind::USER)) {
            $systemResourceKind = new SystemResourceKind(SystemResourceKind::USER);
            $usernameMetadata = SystemMetadata::USERNAME()->toMetadata();
            $usernameDisplayTemplate = '{{r|m' . $usernameMetadata->getName() . '}}';
            $resourceKind = new ResourceKind([], [$usernameMetadata]);
            EntityUtils::forceSetId($resourceKind, $systemResourceKind->getValue());
            $this->handleCommand(
                new ResourceKindUpdateCommand(
                    $resourceKind,
                    ['PL' => 'user', 'EN' => 'user'],
                    [$usernameMetadata],
                    ['header' => $usernameDisplayTemplate, 'dropdown' => $usernameDisplayTemplate]
                )
            );
            $output->writeln("System resource kind user has been created.");
        } else {
            $output->writeln("System resource kind user already exists.");
        }
    }
}
