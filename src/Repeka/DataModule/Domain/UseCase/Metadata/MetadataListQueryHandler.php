<?php
namespace Repeka\DataModule\Domain\UseCase\Metadata;

use Repeka\DataModule\Domain\Entity\Metadata;
use Repeka\DataModule\Domain\Repository\MetadataRepository;

class MetadataListQueryHandler {
    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @return Metadata[]
     */
    public function handle(): array {
        return $this->metadataRepository->findAll();
    }
}
