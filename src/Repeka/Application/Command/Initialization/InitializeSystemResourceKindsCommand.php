<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Application\Entity\EntityUtils;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemResourceKindsCommand extends TransactionalCommand {
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
        foreach (SystemResourceKind::toArray() as $resourceKindName => $resourceKindId) {
            if (!$this->resourceKindRepository->exists($resourceKindId)) {
                $systemResourceKind = new SystemResourceKind($resourceKindId);
                $resourceKind = new ResourceKind([], SystemResourceClass::USER);
                EntityUtils::forceSetId($resourceKind, $systemResourceKind->getValue());
                $label = [];
                $label['PL'] = $label['EN'] = strtolower($resourceKindName);
                $resourceKind->update($label, []);
                $this->resourceKindRepository->save($resourceKind);
                $output->writeln("Resource $resourceKindName has been created.");
            } else {
                $output->writeln("Resource $resourceKindName already exists.");
            }
        }
        $this->idGeneratorHelper->restoreIdGenerator(ResourceKind::class, 'resource_kind_id_seq');
    }
}
