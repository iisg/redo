<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Utils\EntityUtils;

// ResourceEntity because Resource is reserved word in PHP7: http://php.net/manual/en/reserved.other-reserved-words.php
class ResourceEntity implements Identifiable {
    private $id;
    private $kind;
    private $marking;
    private $contents;
    private $resourceClass;

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

    /**
     * Shortcut for getContents()->getValues($metadata)
     * @param Metadata|int $metadata
     * @return mixed[]
     */
    public function getValues($metadata): array {
        return $this->getContents()->getValues($metadata);
    }

    public function hasParent(): bool {
        return isset($this->getContents()[SystemMetadata::PARENT]);
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
        return $this->getWorkflow()->apply($this, $transitionId);
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
}
