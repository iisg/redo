<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceListFtsQueryAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param ResourceListFtsQuery $query
     * @return ResourceListFtsQuery
     */
    public function adjustCommand(Command $query): Command {
        return new ResourceListFtsQuery(
            $query->getPhrase(),
            $this->replaceMetadataNamesOrIdsWithMetadata($query->getSearchableMetadata()),
            $query->getResourceClasses(),
            $query->getPage(),
            $query->getResultsPerPage()
        );
    }

    private function replaceMetadataNamesOrIdsWithMetadata(array $searchableMetadata): array {
        $metadata = [];
        foreach ($searchableMetadata as $metadataNameOrId) {
            if (!$metadataNameOrId instanceof Metadata) {
                $metadata[] = $this->metadataRepository->findByNameOrId($metadataNameOrId);
            } else {
                $metadata[] = $metadataNameOrId;
            }
        }
        return $metadata;
    }
}
