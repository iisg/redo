<?php
namespace Repeka\Domain\Entity\Workflow;

class FluentRestrictingMetadataSelector {
    /** @var int[] */
    private $requiredIds;
    /** @var int[] */
    private $lockedIds;
    /** @var int[] */
    private $assigneeIds;

    /** @var string[] */
    private $selectedFields = [];

    /**
     * @param int[] $requiredIds
     * @param int[] $lockedIds
     * @param int[] $assigneeIds
     */
    public function __construct(array $requiredIds, array $lockedIds, array $assigneeIds) {
        $this->requiredIds = $requiredIds;
        $this->lockedIds = $lockedIds;
        $this->assigneeIds = $assigneeIds;
    }

    public function required(): self {
        $this->selectedFields = array_unique(array_merge($this->selectedFields, ['requiredIds']));
        return $this;
    }

    public function locked(): self {
        $this->selectedFields = array_unique(array_merge($this->selectedFields, ['lockedIds']));
        return $this;
    }

    public function assignees(): self {
        $this->selectedFields = array_unique(array_merge($this->selectedFields, ['assigneeIds']));
        return $this;
    }

    public function all(): self {
        $this->selectedFields = ['requiredIds', 'lockedIds', 'assigneeIds'];
        return $this;
    }

    /** @return int[] */
    public function get(): array {
        $selectedIds = [];
        foreach ($this->selectedFields as $fieldName) {
            $selectedIds = array_merge($selectedIds, $this->$fieldName);
        }
        return array_values(array_unique($selectedIds));
    }
}
