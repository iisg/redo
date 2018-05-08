<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Utils\EntityUtils;

class ContentSearchImportTransform extends AbstractImportTransform {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceRepository $resourceRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function apply(array $values, array $config): array {
        $metadataId = $config['metadata'] ?? null;
        Assertion::notNull($metadataId, 'contentSearch transform require metadata to be configured');
        $resourceIds = array_map(
            function ($searchValue) use ($metadataId) {
                $filters = ResourceContents::fromArray([$metadataId => $searchValue])
                    ->withMetadataNamesMappedToIds($this->metadataRepository);
                $query = ResourceListQuery::builder()->filterByContents($filters)->build();
                $matchedResources = $this->resourceRepository->findByQuery($query);
                return EntityUtils::mapToIds($matchedResources);
            },
            $values
        );
        return ArrayUtils::flatten($resourceIds);
    }
}
