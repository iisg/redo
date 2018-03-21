<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AbstractCommand;

abstract class AbstractListQuery extends AbstractCommand {
    /** @var int */
    private $page;
    /** @var int */
    private $resultsPerPage;

    protected function __construct(int $page, int $resultsPerPage) {
        $this->page = $page;
        $this->resultsPerPage = $resultsPerPage;
    }

    public function paginate(): bool {
        return $this->page != 0;
    }

    public function getPage(): int {
        return $this->page;
    }

    public function getResultsPerPage(): int {
        return $this->resultsPerPage;
    }
}
