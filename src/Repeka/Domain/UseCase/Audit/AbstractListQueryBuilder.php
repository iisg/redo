<?php
namespace Repeka\Domain\UseCase\Audit;

use Assert\Assertion;

abstract class AbstractListQueryBuilder {
    private const REASONABLE_DEFAULT_QUERY_LIMIT = 99;
    protected $page = 1;
    protected $resultsPerPage = self::REASONABLE_DEFAULT_QUERY_LIMIT;

    public function setPage(int $page): self {
        Assertion::greaterOrEqualThan($page, 1, 'The first page is 1.');
        $this->page = $page;
        if ($this->resultsPerPage === self::REASONABLE_DEFAULT_QUERY_LIMIT) {
            $this->resultsPerPage = 10;
        }
        return $this;
    }

    public function setResultsPerPage(int $resultsPerPage): self {
        Assertion::greaterOrEqualThan($resultsPerPage, 1, 'Results per page cannot be lower than 1.');
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }
}
