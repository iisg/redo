<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\EntityUtils;

class ResourceCloneCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceCloneCommand $command): ResourceEntity {
        $resourceToClone = $command->getResource();
        $newContents = $this->stripNonCloneableMetadata($resourceToClone);
        $newContents = $newContents->withReplacedValues(
            SystemMetadata::RESOURCE_LABEL,
            $resourceToClone->getLabel() . ' - clone '
        );
        $resourceToBeAdded = new ResourceEntity($resourceToClone->getKind(), $newContents);
        if ($marking = $resourceToClone->getMarking()) {
            $resourceToBeAdded->setMarking($marking);
        }
        return $this->resourceRepository->save($resourceToBeAdded);
    }

    private function stripNonCloneableMetadata(ResourceEntity $resource): ResourceContents {
        $contents = $resource->getContents();
        $contents = $this->stripValuesOfNonCloneableControls($resource->getKind(), $contents);
        $contents = $this->stripValuesForUniqueMetadata($resource->getKind(), $contents);
        return $contents;
    }

    private function stripValuesForUniqueMetadata(ResourceKind $resourceKind, ResourceContents $contents): ResourceContents {
        $metadata = array_filter(
            $resourceKind->getMetadataList(),
            function (Metadata $metadata) {
                $constraints = $metadata->getConstraints();
                return $constraints['uniqueInResourceClass'] ?? false;
            }
        );
        $metadataIds = EntityUtils::mapToIds($metadata);
        $contentsArray = $contents->toArray();
        foreach ($metadataIds as $id) {
            unset($contentsArray[$id]);
        }
        return ResourceContents::fromArray($contentsArray);
    }

    private function stripValuesOfNonCloneableControls(ResourceKind $resourceKind, ResourceContents $contents): ResourceContents {
        $nonCloneableMetadataIds = EntityUtils::mapToIds($resourceKind->getMetadataByControl(MetadataControl::FILE()));
        $nonCloneableMetadataIds = array_merge(
            $nonCloneableMetadataIds,
            EntityUtils::mapToIds($resourceKind->getMetadataByControl(MetadataControl::DIRECTORY()))
        );
        $contentsArray = $contents->toArray();
        foreach ($nonCloneableMetadataIds as $id) {
            unset($contentsArray[$id]);
        }
        return ResourceContents::fromArray($contentsArray);
    }
}
