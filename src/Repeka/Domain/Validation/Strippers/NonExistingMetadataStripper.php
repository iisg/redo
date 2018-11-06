<?php
namespace Repeka\Domain\Validation\Strippers;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\EntityUtils;

class NonExistingMetadataStripper {

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param array $content
     * @param string $resourceClass
     * @return ResourceWorkflowPlace[]
     */
    public function removeNonExistingMetadata(array $content, string $resourceClass): array {
        $metadataListQuery = MetadataListQuery::builder()
            ->filterByResourceClass($resourceClass)
            ->addSystemMetadataIds(SystemMetadata::toArray())
            ->build();
        $metadata = $this->metadataRepository->findByQuery($metadataListQuery);
        $metadataIds = EntityUtils::mapToIds($metadata);
        return array_map(function ($workflowPlace) use ($metadataIds) {
            $workflowPlace = $workflowPlace instanceof ResourceWorkflowPlace
                ? $workflowPlace->toArray()
                : ResourceWorkflowPlace::fromArray($workflowPlace)->toArray();  // ensure we have all keys
            return new ResourceWorkflowPlace(
                $workflowPlace['label'],
                $workflowPlace['id'],
                array_intersect($workflowPlace['requiredMetadataIds'], $metadataIds),
                array_intersect($workflowPlace['lockedMetadataIds'], $metadataIds),
                array_intersect($workflowPlace['assigneeMetadataIds'], $metadataIds),
                array_intersect($workflowPlace['autoAssignMetadataIds'], $metadataIds),
                $workflowPlace['pluginsConfig']
            );
        }, $content);
    }
}
