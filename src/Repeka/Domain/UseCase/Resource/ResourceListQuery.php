<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceKind;

class ResourceListQuery extends AbstractCommand {
    /** @var ResourceKind[] */
    private $resourceKinds;
    /** @var string[] */
    private $resourceClasses;
    /** @var int */
    private $parentId;
    /** @var int */
    private $page;
    /** @var int */
    private $resultsPerPage;
    /** @var bool */
    private $onlyTopLevel;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    private function __construct(
        array $resourceClasses,
        array $resourceKinds,
        int $parentId,
        int $page,
        int $resultsPerPage,
        bool $onlyTopLevel
    ) {
        $this->resourceKinds = $resourceKinds;
        $this->resourceClasses = $resourceClasses;
        $this->parentId = $parentId;
        $this->page = $page;
        $this->resultsPerPage = $resultsPerPage;
        $this->onlyTopLevel = $onlyTopLevel;
    }

    public static function builder(): ResourceListQueryBuilder {
        return new ResourceListQueryBuilder();
    }

    public static function withParams(
        array $resourceClasses,
        array $resourceKinds,
        int $parentId,
        int $page,
        int $resultsPerPage,
        bool $onlyTopLevel
    ): ResourceListQuery {
        return new self($resourceClasses, $resourceKinds, $parentId, $page, $resultsPerPage, $onlyTopLevel);
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
}
