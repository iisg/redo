<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;

class ResourceListQuery extends AbstractCommand {
    private $ids;
    /** @var ResourceKind[] */
    private $resourceKinds;
    /** @var string[] */
    private $resourceClasses;
    /** @var int */
    private $parentId;
    /** @var bool */
    private $onlyTopLevel;
    /** @var ResourceContents */
    private $contentsFilter;
    /** @var array */
    private $sortBy;
    /** @var int */
    private $page;
    /** @var int */
    private $resultsPerPage;

    private function __construct() {
    }

    public static function builder(): ResourceListQueryBuilder {
        return new ResourceListQueryBuilder();
    }

    public static function withParams(
        array $ids,
        array $resourceClasses,
        array $resourceKinds,
        array $sortBy,
        int $parentId,
        ResourceContents $contentsFilter,
        bool $onlyTopLevel,
        int $page,
        int $resultsPerPage
    ): ResourceListQuery {
        $query = new self();
        $query->ids = $ids;
        $query->resourceKinds = $resourceKinds;
        $query->resourceClasses = $resourceClasses;
        $query->sortBy = $sortBy;
        $query->parentId = $parentId;
        $query->page = $page;
        $query->resultsPerPage = $resultsPerPage;
        $query->contentsFilter = $contentsFilter;
        $query->onlyTopLevel = $onlyTopLevel;
        return $query;
    }

    /** @return int[] */
    public function getIds(): array {
        return $this->ids;
    }

    /** @return string[] */
    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    /** @return ResourceKind[] */
    public function getResourceKinds(): array {
        return $this->resourceKinds;
    }

    public function paginate(): bool {
        return $this->page != 0;
    }

    public function getParentId(): int {
        return $this->parentId;
    }

    public function getSortByMetadataIds(): array {
        return $this->sortBy;
    }

    public function getPage(): int {
        return $this->page;
    }

    public function getResultsPerPage(): int {
        return $this->resultsPerPage;
    }

    public function onlyTopLevel(): bool {
        return $this->onlyTopLevel;
    }

    public function getContentsFilter(): ResourceContents {
        return $this->contentsFilter;
    }
}
