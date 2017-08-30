<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemResourceKindsMetadataCommand extends TransactionalCommand {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceKindRepository $resourceKindRepository) {
        parent::__construct();
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-resource-kinds-metadata')
            ->setDescription('Inserts metadata in system resource kinds.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        $this->initializeMetadataInUser($output);
    }

    private function initializeMetadataInUser(OutputInterface $output) {
        $userResourceKind = $this->resourceKindRepository->findOne(SystemResourceKind::USER);
        try {
            $userResourceKind->getMetadataByBaseId(SystemMetadata::USERNAME);
        } catch (\InvalidArgumentException $e) {
            /** @var Metadata $usernameMetadata */
            $usernameMetadata = $this->metadataRepository->findOne(SystemMetadata::USERNAME);
            $forResource = Metadata::createForResourceKind(
                $usernameMetadata->getLabel(),
                $userResourceKind,
                $usernameMetadata
            );
            $userResourceKind->addMetadata($forResource);
            $this->resourceKindRepository->save($userResourceKind);
            $output->writeln("Added username metadata to user resource kind.");
        }
    }
}
