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

    public function getId(): int {
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
        return $this->contents;
    }

    public function updateContents(array $contents) {
        $this->contents = $contents;
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

    public function canApplyTransition(User $executor, string $transitionId):bool {
        $transition = $this->getWorkflow()->getTransition($transitionId);
        return $transition->canApply($executor);
    }
}
