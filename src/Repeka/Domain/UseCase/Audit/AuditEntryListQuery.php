<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceContents;

class AuditEntryListQuery extends AbstractListQuery implements NonValidatedCommand, AdjustableCommand {
    /** @var array */
    private $commandNames;
    /** @var string */
    private $dateFrom;
    private $dateTo;
    /** @var ResourceContents */
    private $resourceContentsFilter;
    private $resourceId;

    public function __construct(
        array $commandNames,
        string $dateFrom,
        string $dateTo,
        ResourceContents $resourceContentsFilter,
        int $page,
        int $resultsPerPage,
        int $resourceId
    ) {
        parent::__construct($page, $resultsPerPage);
        $this->commandNames = $commandNames;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->resourceContentsFilter = $resourceContentsFilter;
        $this->resourceId = $resourceId;
    }

    public static function builder(): AuditEntryListQueryBuilder {
        return new AuditEntryListQueryBuilder();
    }

    public function getCommandNames(): array {
        return $this->commandNames;
    }

    public function getDateFrom(): string {
        return $this->dateFrom;
    }

    public function getDateTo(): string {
        return $this->dateTo;
    }

    public function getResourceContentsFilter(): ResourceContents {
        return $this->resourceContentsFilter;
    }

    public function getResourceId(): int {
        return $this->resourceId;
    }
}
