<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemResourcesCommand extends TransactionalCommand {
    use CommandBusAware;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var EntityIdGeneratorHelper */
    private $idGeneratorHelper;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(
        EntityIdGeneratorHelper $idGeneratorHelper,
        ResourceRepository $resourceRepository,
        ResourceKindRepository $resourceKindRepository
    ) {
        parent::__construct();
        $this->idGeneratorHelper = $idGeneratorHelper;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-resources')
            ->setDescription('Inserts system resources.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        $this->idGeneratorHelper->preventGeneratingIds(ResourceEntity::class);
        $this->createSystemResources($output);
        $this->idGeneratorHelper->restoreIdGenerator(ResourceEntity::class, 'resource_id_seq');
    }

    private function createSystemResources(OutputInterface $output) {
        foreach (SystemResource::toArray() as $resourceName => $resourceId) {
            if (!$this->resourceRepository->exists($resourceId)) {
                $systemResource = new SystemResource($resourceId);
                $userResourceKind = $resourceId == SystemResource::UNAUTHENTICATED_USER
                    ? $this->resourceKindRepository->findOne(SystemResourceKind::USER)
                    : null;
                $resource = $systemResource->toResource($userResourceKind);
                EntityUtils::forceSetId($resource, $systemResource->getValue());
                $this->resourceRepository->save($resource);
                $output->writeln("Resource $resourceName has been created.");
            }
        }
    }
}
