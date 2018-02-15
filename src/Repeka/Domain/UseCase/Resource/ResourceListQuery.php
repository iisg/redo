<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;

class ResourceListQuery extends AbstractCommand {
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
        array $resourceClasses,
        array $resourceKinds,
        int $parentId,
        ResourceContents $contentsFilter,
        bool $onlyTopLevel,
        int $page,
        int $resultsPerPage
    ): ResourceListQuery {
        $query = new self();
        $query->resourceKinds = $resourceKinds;
        $query->resourceClasses = $resourceClasses;
        $query->parentId = $parentId;
        $query->page = $page;
        $query->resultsPerPage = $resultsPerPage;
        $query->contentsFilter = $contentsFilter;
        $query->onlyTopLevel = $onlyTopLevel;
        return $query;
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
