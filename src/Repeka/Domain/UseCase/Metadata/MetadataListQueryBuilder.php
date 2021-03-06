<?php
namespace Repeka\Domain\UseCase\Metadata;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class MetadataListQueryBuilder {
    private $systemMetadataIds = [];
    private $ids = [];
    private $names = [];
    /** @var ?Metadata */
    private $parent;
    private $resourceClasses;
    private $onlyTopLevel = false;
    private $controls;
    private $requiredKindIds = [];
    private $excludedIds = [];

    public function addSystemMetadataIds(array $systemMetadataIds): MetadataListQueryBuilder {
        $this->systemMetadataIds = $systemMetadataIds;
        return $this;
    }

    public function filterByIds(array $ids): MetadataListQueryBuilder {
        $this->ids = $ids;
        return $this;
    }

    public function filterByName(string $name): MetadataListQueryBuilder {
        return $this->filterByNames([$name]);
    }

    public function filterByNames(array $names): MetadataListQueryBuilder {
        $this->names = $names;
        return $this;
    }

    public function filterByParent(Metadata $parent): MetadataListQueryBuilder {
        Assertion::false($this->onlyTopLevel, 'Cannot set parent for onlyTopLevel query.');
        $this->parent = $parent;
        return $this;
    }

    public function filterByResourceClass(string $resourceClass): MetadataListQueryBuilder {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    /** @param string[] $resourceClasses */
    public function filterByResourceClasses(array $resourceClasses): MetadataListQueryBuilder {
        $this->resourceClasses = $resourceClasses;
        return $this;
    }

    public function filterByControl(MetadataControl $control): MetadataListQueryBuilder {
        return $this->filterByControls([$control]);
    }

    /** @param MetadataControl[] $controls */
    public function filterByControls(array $controls): MetadataListQueryBuilder {
        $this->controls = $controls;
        return $this;
    }

    public function filterByRequiredKindIds(array $requiredKindIds): MetadataListQueryBuilder {
        $this->requiredKindIds = $requiredKindIds;
        return $this;
    }

    public function excludeIds(array $excludedIds): MetadataListQueryBuilder {
        $this->excludedIds = $excludedIds;
        return $this;
    }

    public function onlyTopLevel(): MetadataListQueryBuilder {
        Assertion::null($this->parent, 'Cannot set onlyTopLevel for query filtered by parent.');
        $this->onlyTopLevel = true;
        return $this;
    }

    public function build(): MetadataListQuery {
        return MetadataListQuery::withParams(
            $this->ids,
            $this->names,
            $this->resourceClasses,
            $this->parent,
            $this->controls,
            $this->onlyTopLevel,
            $this->systemMetadataIds,
            $this->requiredKindIds,
            $this->excludedIds
        );
    }
}
