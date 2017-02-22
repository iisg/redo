<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataChildCreateCommandHandler {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataChildCreateCommand $command): Metadata {
        $metadata = Metadata::createChild($command->getBase(), $command->getParent());
        return $this->metadataRepository->save($metadata);
    }
}
