<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceTreeQuery extends AbstractListQuery implements AdjustableCommand {
    /** @var int */
    private $rootId;
    /** @var int */
    private $depth;
    /** @var int */
    private $siblings;
    /** @var string[] */
    private $resourceClasses;
    /** @var ResourceKind[] | int[] */
    private $resourceKinds;
    /** @var ResourceContents */
    private $contentsFilter;
    /** @var bool */
    private $oneMoreElements;

    protected function __construct(int $page, int $resultsPerPage) {
        parent::__construct($page, $resultsPerPage);
    }

    public static function builder(): ResourceTreeQueryBuilder {
        return new ResourceTreeQueryBuilder();
    }

    public static function withParams(
        int $rootId,
        int $depth,
        int $siblings,
        array $resourceClasses,
        array $resourceKinds,
        $contentsFilter,
        int $page,
        int $resultsPerPage,
        bool $oneMoreElements
    ): ResourceTreeQuery {
        $query = new self($page, $resultsPerPage);
        $query->rootId = $rootId;
        $query->depth = $depth;
        $query->siblings = $siblings;
        $query->resourceClasses = $resourceClasses;
        $query->resourceKinds = $resourceKinds;
        $query->contentsFilter = $contentsFilter;
        $query->oneMoreElements = $oneMoreElements;
        return $query;
    }

    /** @return int */
    public function getRootId(): int {
        return $this->rootId;
    }

    /** @return bool */
    public function hasRootId(): bool {
        return $this->rootId !== 0;
    }

    /** @return int */
    public function getDepth(): int {
        return $this->depth;
    }

    /** @return bool */
    public function hasDepth(): bool {
        return $this->depth !== 0;
    }

    /** @return int */
    public function getSiblings(): int {
        return $this->siblings;
    }

    /** @return bool */
    public function hasSiblings(): bool {
        return $this->siblings !== 0;
    }

    /** @return string[] */
    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    /** @return ResourceKind[] */
    public function getResourceKinds(): array {
        return $this->resourceKinds;
    }

    /** @return ResourceContents */
    public function getContentsFilter(): ResourceContents {
        return $this->contentsFilter;
    }

    /** @return bool */
    public function oneMoreElements(): bool {
        return $this->oneMoreElements;
    }
}
