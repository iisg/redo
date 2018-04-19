<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Entity\ResourceContents;

class AuditEntryListQueryBuilder extends AbstractListQueryBuilder {
    private $commandNames = [];
    private $resourceContents = [];

    public function build(): AuditEntryListQuery {
        return new AuditEntryListQuery(
            $this->commandNames,
            $this->resourceContents instanceof ResourceContents
                ? $this->resourceContents
                : ResourceContents::fromArray($this->resourceContents),
            $this->page,
            $this->resultsPerPage
        );
    }

    public function filterByCommandNames(array $commandNames): self {
        $this->commandNames = $commandNames;
        return $this;
    }

    /** @param ResourceContents|array $resourceContents */
    public function filterByResourceContents($resourceContents): self {
        $this->resourceContents = $resourceContents;
        return $this;
    }
}
