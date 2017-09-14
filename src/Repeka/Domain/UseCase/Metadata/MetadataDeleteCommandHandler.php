<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Repository\MetadataRepository;

class MetadataDeleteCommandHandler {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataDeleteCommand $command): void {
        $this->metadataRepository->delete($command->getMetadata());
    }
}
