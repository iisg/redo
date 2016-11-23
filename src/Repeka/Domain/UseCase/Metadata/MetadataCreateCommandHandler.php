<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataCreateCommandHandler {
    /** @var MetadataFactory */
    private $metadataFactory;

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataFactory $metadataFactory, MetadataRepository $metadataRepository) {
        $this->metadataFactory = $metadataFactory;
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataCreateCommand $command): Metadata {
        $metadata = $this->metadataFactory->create($command);
        return $this->metadataRepository->save($metadata);
    }
}
