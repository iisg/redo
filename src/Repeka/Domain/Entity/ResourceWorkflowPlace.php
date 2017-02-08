<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowPlace {
    private $id;
    private $label;

    public function __construct(array $label, $id = null) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
    }

    public function getId(): string {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
        ];
    }

    public static function fromArray(array $data) {
        return new self($data['label'], $data['id'] ?? null);
    }
}
