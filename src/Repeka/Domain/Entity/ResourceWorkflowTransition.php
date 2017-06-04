<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowTransition {
    private $id;
    private $label;
    private $fromIds;
    private $toIds;
    private $permittedRoleIds;

    public function __construct(array $label, array $fromIds, array $toIds, array $permittedRoleIds = [], $id = null) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
        $this->fromIds = $fromIds;
        $this->toIds = $toIds;
        $this->permittedRoleIds = $permittedRoleIds;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /** @return string[] */
    public function getFromIds(): array {
        return $this->fromIds;
    }

    /** @return string[] */
    public function getToIds(): array {
        return $this->toIds;
    }

    public function getPermittedRoleIds(): array {
        return $this->permittedRoleIds;
    }

    /** @param ResourceWorkflowPlace[] $permittedPlaces */
    public function canEnterTos(array $permittedPlaces) {
        $permittedIds = array_map(function ($place) {
            return $place->getId();
        }, $permittedPlaces);
        $extraneousIds = array_diff($this->getToIds(), $permittedIds);
        return count($extraneousIds) == 0;
    }

    public function userHasRoleRequiredToApply(User $user): bool {
        foreach ($this->getPermittedRoleIds() as $permittedRoleId) {
            if ($user->hasRole($permittedRoleId)) {
                return true;
            }
        }
        return false;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'froms' => $this->getFromIds(),
            'tos' => $this->getToIds(),
            'permittedRoleIds' => $this->getPermittedRoleIds(),
        ];
    }

    public static function fromArray(array $data) {
        return new self($data['label'], $data['froms'], $data['tos'], $data['permittedRoleIds'] ?? [], $data['id'] ?? null);
    }
}
