<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;

class MetadataCreateCommand extends AbstractCommand implements AdjustableCommand {
    private $name;
    private $label;
    private $description;
    private $placeholder;
    private $controlName;
    private $constraints;
    private $shownInBrief;
    private $copyToChildResource;
    private $resourceClass;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @param string $name
     * @param array $label
     * @param array $description
     * @param array $placeholder
     * @param string $controlName
     * @param string $resourceClass
     * @param array $constraints
     * @param bool $shownInBrief
     * @param bool $copyToChildResource
     */
    public function __construct(
        string $name,
        array $label,
        array $description,
        array $placeholder,
        string $controlName,
        string $resourceClass,
        array $constraints = [],
        bool $shownInBrief = false,
        bool $copyToChildResource = false
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->controlName = $controlName;
        $this->constraints = $constraints;
        $this->shownInBrief = $shownInBrief;
        $this->copyToChildResource = $copyToChildResource;
        $this->resourceClass = $resourceClass;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getDescription(): array {
        return $this->description;
    }

    public function getPlaceholder(): array {
        return $this->placeholder;
    }

    public function getControlName(): string {
        return $this->controlName;
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function isShownInBrief(): bool {
        return $this->shownInBrief;
    }

    public function isCopiedToChildResource(): bool {
        return $this->copyToChildResource;
    }

    public static function fromArray(array $data): MetadataCreateCommand {
        return new MetadataCreateCommand(
            $data['name'] ?? '',
            $data['label'] ?? [],
            $data['description'] ?? [],
            $data['placeholder'] ?? [],
            $data['control'] ?? 'text',
            $data['resourceClass'] ?? '',
            $data['constraints'] ?? [],
            $data['shownInBrief'] ?? false,
            $data['copyToChildResource'] ?? false
        );
    }
}
