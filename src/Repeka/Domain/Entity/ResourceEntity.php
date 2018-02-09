<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;

// ResourceEntity because Resource is reserved word in PHP7: http://php.net/manual/en/reserved.other-reserved-words.php
class ResourceEntity implements Identifiable {
    private $id;
    private $kind;
    private $marking;
    private $contents;
    private $resourceClass;

    public function __construct(ResourceKind $kind, array $contents) {
        $this->kind = $kind;
        $this->contents = $contents;
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

    public function getContents(): array {
        return array_filter($this->contents, function ($values) {
            return count($values) > 0;
        });
    }

    public function hasParent(): bool {
        return isset($this->getContents()[SystemMetadata::PARENT]);
    }

    public function getValues(Metadata $metadata): array {
        Assertion::inArray($metadata->getId(), $this->kind->getMetadataIds());
        return $this->getContents()[$metadata->getId()] ?? [];
    }

    public function updateContents(array $contents) {
        $this->contents = array_filter($contents, function ($values) {
            return count($values) > 0;
        });
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
}
