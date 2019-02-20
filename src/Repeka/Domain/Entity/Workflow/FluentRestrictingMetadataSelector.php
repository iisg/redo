<?php
namespace Repeka\Domain\Entity\Workflow;

use Repeka\Domain\Entity\ResourceKind;

class FluentRestrictingMetadataSelector {
    /** @var int[] */
    private $requiredIds;
    /** @var int[] */
    private $lockedIds;
    /** @var int[] */
    private $assigneeIds;
    /** @var int[] */
    private $autoAssignIds;

    /** @var string[] */
    private $selectedIds = [];

    /**
     * @param int[] $requiredIds
     * @param int[] $lockedIds
     * @param int[] $assigneeIds
     * @param int[] $autoAssignIds
     */
    public function __construct(array $requiredIds, array $lockedIds, array $assigneeIds, array $autoAssignIds) {
        $this->requiredIds = $requiredIds;
        $this->lockedIds = $lockedIds;
        $this->assigneeIds = $assigneeIds;
        $this->autoAssignIds = $autoAssignIds;
    }

    public function required(): self {
        $this->selectedIds = array_merge($this->selectedIds, $this->requiredIds);
        return $this;
    }

    public function locked(): self {
        $this->selectedIds = array_merge($this->selectedIds, $this->lockedIds);
        return $this;
    }

    public function assignees(): self {
        $this->selectedIds = array_merge($this->selectedIds, $this->assigneeIds);
        return $this;
    }

    public function autoAssign(): self {
        $this->selectedIds = array_merge($this->selectedIds, $this->autoAssignIds);
        return $this;
    }

    public function existingInResourceKind(ResourceKind $resourceKind): self {
        $this->selectedIds = array_intersect($resourceKind->getMetadataIds(), $this->selectedIds);
        return $this;
    }

    public function all(): self {
        $this->selectedIds = array_merge($this->requiredIds, $this->lockedIds, $this->assigneeIds, $this->autoAssignIds);
        return $this;
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @return int[]
     */
    public function get($onlyUnique = true): array {
        if ($onlyUnique) {
            return array_values(array_unique($this->selectedIds));
        } else {
            return array_values($this->selectedIds);
        }
    }
}
