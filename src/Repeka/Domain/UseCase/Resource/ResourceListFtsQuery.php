<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceListFtsQuery extends AbstractListQuery implements NonValidatedCommand, AdjustableCommand {
    use RequireOperatorRole;

    /** @var string */
    private $phrase;
    /** @var array */
    private $searchableMetadata;
    /** @var array */
    private $resourceClasses;

    public function __construct(
        string $phrase,
        array $searchableMetadata,
        array $resourceClasses = [],
        int $page = 0,
        int $resultsPerPage = 10
    ) {
        parent::__construct($page, $resultsPerPage);
        $this->phrase = $phrase;
        $this->searchableMetadata = $searchableMetadata;
        $this->resourceClasses = $resourceClasses;
    }

    public function getPhrase(): string {
        return $this->phrase;
    }

    /** @return Metadata[] */
    public function getSearchableMetadata(): array {
        return $this->searchableMetadata;
    }

    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    public function getRequiredRole(): ?SystemRole {
        return null;
    }
}
