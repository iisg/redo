<?php
namespace Repeka\Domain\UseCase;

class TreeResult {
    /** @var array */
    private $treeContents;
    /** @var array */
    private $matching;
    /** @var int */
    private $topLevelPageNumber;
    /** @var int */
    private $siblings;

    public function __construct(array $treeContents = [], array $matching = [], int $topLevelPageNumber = 1, int $siblings = 0) {
        $this->treeContents = $treeContents;
        $this->matching = $matching;
        $this->topLevelPageNumber = $topLevelPageNumber;
        $this->siblings = $siblings;
    }

    public function getTreeContents(): array {
        return $this->treeContents;
    }

    public function getMatchingIds(): array {
        return $this->matching;
    }

    public function getTopLevelPageNumber(): int {
        return $this->topLevelPageNumber;
    }

    public function getSiblings(): int {
        return $this->siblings;
    }
}
