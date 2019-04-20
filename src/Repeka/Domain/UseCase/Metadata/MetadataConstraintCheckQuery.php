<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class MetadataConstraintCheckQuery extends AbstractCommand implements AdjustableCommand, NonValidatedCommand {
    use RequireNoRoles;

    /** @var mixed */
    private $value;
    /** @var AbstractMetadataConstraint|string */
    private $constraint;
    /** @var int|Metadata|string */
    private $metadata;
    /** @var int|ResourceEntity */
    private $resource;
    /** @var array|ResourceContents */
    private $currentContents;

    /**
     * @param string|AbstractMetadataConstraint $constraint
     * @param mixed $value
     * @param int|string|Metadata $metadata
     * @param ResourceEntity $resource
     * @param array|ResourceContents $currentContents
     */
    public function __construct($constraint, $value, $metadata, ResourceEntity $resource, $currentContents) {
        $this->constraint = $constraint;
        $this->value = $value;
        $this->metadata = $metadata;
        $this->resource = $resource;
        $this->currentContents = $currentContents;
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

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    /** @return ResourceContents */
    public function getCurrentContents() {
        return $this->currentContents;
    }
}
