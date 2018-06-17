<?php
namespace Repeka\Domain\Entity\Workflow;

use Cocur\Slugify\Slugify;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\Labeled;

class ResourceWorkflowTransition implements Identifiable, Labeled {
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

    /** @return string[] */
    public function getFromIds(): array {
        return $this->fromIds;
    }

    /** @return string[] */
    public function getToIds(): array {
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
        return new self(
            $data['label'] ?? [],
            $data['froms'] ?? [],
            $data['tos'] ?? [],
            $data['id'] ?? null
        );
    }
}
