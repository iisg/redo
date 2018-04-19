<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceContents;

class AuditEntryListQuery extends AbstractListQuery implements NonValidatedCommand, AdjustableCommand {
    /** @var array */
    private $commandNames;
    /** @var ResourceContents */
    private $resourceContentsFilter;

    public function __construct(array $commandNames, ResourceContents $resourceContentsFilter, int $page, int $resultsPerPage) {
        parent::__construct($page, $resultsPerPage);
        $this->commandNames = $commandNames;
        $this->resourceContentsFilter = $resourceContentsFilter;
    }

    public static function builder(): AuditEntryListQueryBuilder {
        return new AuditEntryListQueryBuilder();
    }

    public function getCommandNames(): array {
        return $this->commandNames;
    }

    public function getResourceContentsFilter(): ResourceContents {
        return $this->resourceContentsFilter;
    }
}
