<?php
namespace Repeka\Domain\UseCase;

class PageResult {

    /** @var array */
    private $results;
    /** @var int */
    private $totalCount;

    public function __construct(array $results = [], int $totalCount = 0) {
        $this->results = $results;
        $this->totalCount = $totalCount;
    }

    public function getResults(): array {
        return $this->results;
    }

    public function getTotalCount(): int {
        return $this->totalCount;
    }
}
