<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\CoreModule\Domain\Validator\AnnotationBasedValidator;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataCreateCommandHandler {
    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataCreateCommand $command): Metadata {
        $metadata = $this->toMetadata($command);
        return $this->metadataRepository->save($metadata);
    }

    private function toMetadata(MetadataCreateCommand $command): Metadata {
        $metadata = new Metadata($command->getControl(), $command->getName(), $command->getLabel());
        return $metadata
            ->setPlaceholder($command->getPlaceholder())
            ->setDescription($command->getDescription());
    }
}
