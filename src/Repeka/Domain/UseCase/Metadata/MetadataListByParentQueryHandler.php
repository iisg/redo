<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataListByParentQueryHandler {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /** @return Metadata[] */
    public function handle(MetadataListByParentQuery $query): array {
        return $this->metadataRepository->findByParent($query->getParent());
    }
}
