<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataChildCreateCommandHandler {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataFactory */
    private $metadataFactory;

    public function __construct(MetadataFactory $metadataFactory, MetadataRepository $metadataRepository) {
        $this->metadataFactory = $metadataFactory;
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataChildCreateCommand $command): Metadata {
        $metadata = $this->metadataFactory->createWithParent($command->getNewChildMetadata(), $command->getParent());
        return $this->metadataRepository->save($metadata);
    }
}
