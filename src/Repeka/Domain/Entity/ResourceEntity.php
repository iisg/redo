<?php
namespace Repeka\Domain\Entity;

// ResourceEntity because Resource is reserved word in PHP7: http://php.net/manual/en/reserved.other-reserved-words.php
use Assert\Assertion;

class ResourceEntity {
    private $id;
    private $kind;
    private $marking;
    private $contents;

    public function __construct(ResourceKind $kind, array $contents) {
        $this->kind = $kind;
        $this->contents = $contents;
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

    public function getValues(Metadata $metadata): array {
        $baseId = $metadata->isBase() ? $metadata->getId() : $metadata->getBaseId();
        $baseIds = array_map(function (Metadata $m) {
            return $m->getBaseId();
        }, $this->kind->getMetadataList());
        Assertion::inArray($baseId, $baseIds);
        return $this->getContents()[$baseId] ?? [];
    }

    public function updateContents(array $contents) {
        $this->contents = array_filter($contents, function ($values) {
            return count($values) > 0;
        });
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
