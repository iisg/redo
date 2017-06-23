<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataUpdateCommandHandler {
    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataUpdateCommand $command): Metadata {
        $metadata = $this->metadataRepository->findOne($command->getMetadataId());
        $metadata->update(
            $command->getNewLabel(),
            $command->getNewPlaceholder(),
            $command->getNewDescription(),
            $command->getNewConstraints(),
            $command->getNewShownInBrief()
        );
        return $this->metadataRepository->save($metadata);
    }
}
