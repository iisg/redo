<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Repository\MetadataRepository;

class MetadataListByResourceClassQueryHandler {
    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataListByResourceClassQuery $query): array {
        return $this->metadataRepository->findTopLevelByResourceClass($query->getResourceClass());
    }
}
