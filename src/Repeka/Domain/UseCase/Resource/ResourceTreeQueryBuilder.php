<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class ResourceTreeQueryBuilder extends AbstractListQueryBuilder {
    private $rootId = 0;
    private $depth = 0;
    private $siblings = 0;
    private $resourceClasses = [];
    private $resourceKinds = [];
    private $contents = [];
    private $oneMoreElements = false;

    public function forRootId(int $rootId): ResourceTreeQueryBuilder {
        $this->rootId = $rootId;
        return $this;
    }

    public function includeWithinDepth(int $depth): ResourceTreeQueryBuilder {
        $this->depth = $depth;
        return $this;
    }

    public function setSiblings(int $siblings): ResourceTreeQueryBuilder {
        $this->siblings = $siblings;
        return $this;
    }

    public function filterByResourceClasses(array $resourceClasses): ResourceTreeQueryBuilder {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByResourceClass(string $resourceClass): ResourceTreeQueryBuilder {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    /** @param ResourceKind[] | int[] $resourceKinds */
    public function filterByResourceKinds(array $resourceKinds): ResourceTreeQueryBuilder {
        $this->resourceKinds = array_values(array_merge($this->resourceKinds, $resourceKinds));
        return $this;
    }

    public function filterByResourceKind(ResourceKind $resourceKind): ResourceTreeQueryBuilder {
        return $this->filterByResourceKinds([$resourceKind]);
    }

    /**
     * Unlike in @ResourceListQueryBuilder, each call replaces filter in query. By default there's none.
     */
    public function filterByContents($contents): ResourceTreeQueryBuilder {
        $this->contents = $contents instanceof ResourceContents ? $contents : ResourceContents::fromArray($contents);
        return $this;
    }

    public function oneMoreElements(): ResourceTreeQueryBuilder {
        $this->oneMoreElements = true;
        return $this;
    }

    public function build(): ResourceTreeQuery {
        return ResourceTreeQuery::withParams(
            $this->rootId,
            $this->depth,
            $this->siblings,
            $this->resourceClasses,
            $this->resourceKinds,
            $this->contents ?: ResourceContents::empty(),
            $this->page,
            $this->resultsPerPage,
            $this->oneMoreElements
        );
    }
}
