<?php
namespace Repeka\Domain\UseCase\Metadata;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class MetadataListQueryBuilder {
    private $ids = [];
    /** @var ?Metadata */
    private $parent;
    private $resourceClasses;
    private $onlyTopLevel = false;
    private $controls;

    public function filterByIds(array $ids): MetadataListQueryBuilder {
        $this->ids = $ids;
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

    public function onlyTopLevel(): MetadataListQueryBuilder {
        Assertion::null($this->parent, 'Cannot set onlyTopLevel for query filtered by parent.');
        $this->onlyTopLevel = true;
        return $this;
    }

    public function build(): MetadataListQuery {
        return MetadataListQuery::withParams($this->ids, $this->resourceClasses, $this->parent, $this->controls, $this->onlyTopLevel);
    }
}
