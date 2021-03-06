<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Factory\BulkChanges\PendingUpdates;
use Repeka\Domain\Service\ResourceDisplayStrategyDependencyMap;
use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
// ResourceEntity because Resource is reserved word in PHP7: http://php.net/manual/en/reserved.other-reserved-words.php
class ResourceEntity implements Identifiable, HasResourceClass {
    private $id;
    private $kind;
    private $marking;
    private $contents;
    private $resourceClass;
    private $displayStrategyDependencies = [];
    private $displayStrategiesDirty = true;
    private $pendingUpdates = [];

    public function __construct(ResourceKind $kind, ResourceContents $contents) {
        $this->kind = $kind;
        $this->updateContents($contents);
        $this->resourceClass = $kind->getResourceClass();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getMarking() {
        return $this->marking;
    }

    public function setMarking(array $marking) {
        $this->marking = $marking;
    }

    public function getContents(): ResourceContents {
        return new ResourceContents($this->contents);
    }

    public function getPendingUpdates(): PendingUpdates {
        return new PendingUpdates($this->pendingUpdates);
    }

    /** @param $updates PendingUpdates|array */
    public function setPendingUpdates($updates) {
        $this->pendingUpdates = is_object($updates) ? $updates->toArray() : $updates;
    }

    public function getTeaser(): ResourceContents {
        $strippedContents = [
            SystemMetadata::RESOURCE_LABEL => $this->getValues(SystemMetadata::RESOURCE_LABEL),
            SystemMetadata::PARENT => $this->getValues(SystemMetadata::PARENT),
        ];
        if ($this->getKind()->getId() == SystemResourceKind::USER) {
            $strippedContents[SystemMetadata::USERNAME] = $this->getValues(SystemMetadata::USERNAME);
        }
        return ResourceContents::fromArray($strippedContents);
    }

    /**
     * Shortcut for getContents()->getValues($metadata)
     * @param Metadata|int $metadata
     * @return MetadataValue[]
     */
    public function getValues($metadata): array {
        return $this->getContents()->getValues($metadata);
    }

    /**
     * Shortcut for getContents()->getValuesWithoutSubmetadata($metadata)
     * @param Metadata|int $metadata
     * @return mixed[]
     */
    public function getValuesWithoutSubmetadata($metadata): array {
        return $this->getContents()->getValuesWithoutSubmetadata($metadata);
    }

    public function hasParent(): bool {
        return !empty($this->getContents()->getValuesWithoutSubmetadata(SystemMetadata::PARENT));
    }

    public function getParentId(): int {
        return current($this->getContents()->getValuesWithoutSubmetadata(SystemMetadata::PARENT));
    }

    public function getLabel(): string {
        return current($this->getContents()->getValuesWithoutSubmetadata(SystemMetadata::RESOURCE_LABEL));
    }

    public function updateContents(ResourceContents $contents) {
        $this->contents = $contents->filterOutEmptyMetadata()->toArray();
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function hasWorkflow() {
        return $this->getWorkflow() != null;
    }

    public function getWorkflow(): ?ResourceWorkflow {
        return $this->kind->getWorkflow();
    }

    public function applyTransition(string $transitionId): ResourceEntity {
        Assertion::true($this->hasWorkflow(), 'Could not apply transition for resource without a workflow. ID: ' . $this->id);
        if (SystemTransition::isValid($transitionId)) {
            $transition = new SystemTransition($transitionId);
            $transition->apply($this);
            return $this;
        } else {
            return $this->getWorkflow()->apply($this, $transitionId);
        }
    }

    /** @return ResourceWorkflowPlace[] */
    public function getCurrentPlaces(): array {
        return $this->getWorkflow()->getPlaces($this);
    }

    public function getCurrentPlace(): ResourceWorkflowPlace {
        $places = $this->getCurrentPlaces();
        Assertion::count($places, 1, 'Resource is currently in more than one place.');
        return $places[0];
    }

    public function updateDisplayStrategyDependencies(int $metadataId, ResourceDisplayStrategyUsedMetadataCollector $collector) {
        $this->displayStrategyDependencies = (new ResourceDisplayStrategyDependencyMap($this->displayStrategyDependencies))
            ->clear($metadataId)
            ->merge(new ResourceDisplayStrategyDependencyMap($metadataId, $collector))
            ->toArray();
    }

    public function getDependentMetadataIds(ResourceEntity $resource, array $changedMetadataIds): array {
        return (new ResourceDisplayStrategyDependencyMap($this->displayStrategyDependencies))
            ->getDependentMetadataIds($resource, $changedMetadataIds);
    }

    public function isDisplayStrategiesDirty(): bool {
        return $this->displayStrategiesDirty;
    }

    public function clearDisplayStrategiesDirty() {
        $this->displayStrategiesDirty = false;
    }

    public function getAuditData(): array {
        return [
            'resource' => [
                'id' => $this->getId(),
                'kindId' => $this->getKind()->getId(),
                'contents' => $this->contents,
                'resourceClass' => $this->getResourceClass(),
                'places' => $this->hasWorkflow() ? EntityUtils::mapToIds($this->getWorkflow()->getPlaces($this)) : [],
            ],
        ];
    }

    public function isTeaserVisibleFor(UserEntity $user): bool {
        return $this->isUserReferencedInMetadata($user, SystemMetadata::TEASER_VISIBILITY);
    }

    public function isVisibleFor(UserEntity $user): bool {
        return $this->isUserReferencedInMetadata($user, SystemMetadata::VISIBILITY);
    }

    public function isUserReferencedInMetadata(UserEntity $user, $metadataOrId): bool {
        $metadataId = $metadataOrId instanceof Metadata ? $metadataOrId->getId() : $metadataOrId;
        $allowedViewers = $this->getContents()->getValuesWithoutSubmetadata($metadataId);
        return !empty(array_intersect($user->getGroupIdsWithUserId(), $allowedViewers));
    }
}
