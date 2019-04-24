<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class MetadataConstraintCheckQuery extends AbstractCommand implements AdjustableCommand, NonValidatedCommand {
    use RequireNoRoles;

    /** @var mixed */
    private $value;
    /** @var AbstractMetadataConstraint|string */
    private $constraint;
    /** @var int|Metadata|string */
    private $metadata;
    /** @var ResourceEntity|null */
    private $resource;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var array|ResourceContents */
    private $currentContents;

    /**
     * @param string|AbstractMetadataConstraint $constraint
     * @param mixed $value
     * @param int|string|Metadata $metadata
     * @param array|ResourceContents $currentContents
     * @param ResourceEntity|ResourceKind $resourceOrKind
     */
    public function __construct($constraint, $value, $metadata, $currentContents, $resourceOrKind) {
        $this->constraint = $constraint;
        $this->value = $value;
        $this->metadata = $metadata;
        $this->currentContents = $currentContents;
        if ($resourceOrKind instanceof ResourceEntity) {
            $this->resource = $resourceOrKind;
            $this->resourceKind = $resourceOrKind->getKind();
        } elseif ($resourceOrKind instanceof ResourceKind) {
            $this->resourceKind = $resourceOrKind;
        } else {
            throw new \InvalidArgumentException(
                '$resourceOrKind must be ResourceEntity or ResourceKind, given: ' .
                (is_object($resourceOrKind) ? get_class($resourceOrKind) : null)
            );
        }
    }

    /** @return mixed */
    public function getValue() {
        return $this->value;
    }

    /** @return AbstractMetadataConstraint */
    public function getConstraint() {
        return $this->constraint;
    }

    /** @return Metadata */
    public function getMetadata() {
        return $this->metadata;
    }

    public function getResource(): ?ResourceEntity {
        return $this->resource;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }

    /** @return ResourceContents */
    public function getCurrentContents() {
        return $this->currentContents;
    }
}
