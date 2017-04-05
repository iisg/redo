<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataChildWithBaseCreateCommandHandler {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataFactory */
    private $metadataFactory;

    public function __construct(MetadataRepository $metadataRepository, MetadataFactory $metadataFactory) {
        $this->metadataRepository = $metadataRepository;
        $this->metadataFactory = $metadataFactory;
    }

    public function handle(MetadataChildWithBaseCreateCommand $command): Metadata {
        $metadata = $this->metadataFactory->createWithBaseAndParent(
            $command->getBase(),
            $command->getParent(),
            $command->getNewChildMetadata()
        );
        return $this->metadataRepository->save($metadata);
    }
}
