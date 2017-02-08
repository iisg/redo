<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowTransition {
    private $id;
    private $label;
    private $fromIds;
    private $toIds;

    public function __construct(array $label, array $fromIds, array $toIds, $id = null) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
        $this->fromIds = $fromIds;
        $this->toIds = $toIds;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getFromIds() {
        return $this->fromIds;
    }

    public function getToIds() {
        return $this->toIds;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'froms' => $this->getFromIds(),
            'tos' => $this->getToIds(),
        ];
    }

    public static function fromArray(array $data) {
        return new self($data['label'], $data['froms'], $data['tos'], $data['id'] ?? null);
    }
}
