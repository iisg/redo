<?php
namespace Repeka\Domain\UseCase\Audit;

use Assert\Assertion;

abstract class AbstractListQueryBuilder {
    protected $page = 0;
    protected $resultsPerPage = 10;

    public function setPage(int $page): self {
        Assertion::greaterOrEqualThan($page, 1, 'The first page is 1.');
        $this->page = $page;
        return $this;
    }

    public function setResultsPerPage(int $resultsPerPage): self {
        Assertion::greaterOrEqualThan($resultsPerPage, 1, 'Results per page cannot be lower than 1.');
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }
}
