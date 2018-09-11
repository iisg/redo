<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\UseCase\ColumnSortDataConverter;

class ResourceKindListQueryAdjuster implements CommandAdjuster {

    /** @var ColumnSortDataConverter */
    private $columnSortDataConverter;

    public function __construct(ColumnSortDataConverter $columnSortDataConverter) {
        $this->columnSortDataConverter = $columnSortDataConverter;
    }

    /**
     * @param ResourceKindListQuery $query
     * @return ResourceKindListQuery
     */
    public function adjustCommand(Command $query): Command {
        return ResourceKindListQuery::withParams(
            $query->getIds(),
            $query->getResourceClasses(),
            $query->getMetadataId(),
            $query->getName(),
            $query->getPage(),
            $query->getResultsPerPage(),
            $this->columnSortDataConverter->convertSortByMetadataColumnsToIntegers($query->getSortBy())
        );
    }
}
