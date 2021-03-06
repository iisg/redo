<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemMetadataCommand extends TransactionalCommand {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var EntityIdGeneratorHelper */
    private $idGeneratorHelper;

    public function __construct(EntityIdGeneratorHelper $idGeneratorHelper, MetadataRepository $metadataRepository) {
        parent::__construct();
        $this->idGeneratorHelper = $idGeneratorHelper;
        $this->metadataRepository = $metadataRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-metadata')
            ->setDescription('Inserts system metadata.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        $this->idGeneratorHelper->preventGeneratingIds(Metadata::class);
        foreach (SystemMetadata::toArray() as $metadataName => $metadataId) {
            if (!$this->metadataRepository->exists($metadataId)) {
                $systemMetadata = new SystemMetadata($metadataId);
                $metadata = $systemMetadata->toMetadata();
                if ($metadataId == SystemMetadata::RESOURCE_LABEL_LANGUAGE) {
                    // https://stackoverflow.com/questions/15586495/doctrine-many-to-one-relationship-persist-operation
                    $parent = $this->metadataRepository->findOne(SystemMetadata::RESOURCE_LABEL);
                    $metadata->setParent($parent);
                }
                EntityUtils::forceSetId($metadata, $systemMetadata->getValue());
                $this->metadataRepository->save($metadata);
                $output->writeln("Metadata $metadataName has been created.");
            }
        }
        $this->idGeneratorHelper->restoreIdGenerator(Metadata::class, 'metadata_id_seq');
    }
}
