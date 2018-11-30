<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Utils\ArrayUtils;

class Paginator {
    private $firstPagesNumber;
    private $lastPagesNumber;
    private $leftPagesNumber;
    private $rightPagesNumber;

    public function __construct(int $firstPagesNumber = 2, int $lastPagesNumber = 2, int $leftPagesNumber = 3, int $rightPagesNumber = 3) {
        $this->firstPagesNumber = $firstPagesNumber;
        $this->lastPagesNumber = $lastPagesNumber;
        $this->leftPagesNumber = $leftPagesNumber;
        $this->rightPagesNumber = $rightPagesNumber;
    }

    public static function totalPagesFromResultCounts($resultsPerPage, $totalResults) {
        return intval(ceil($totalResults / $resultsPerPage));
    }

    public function paginate($currentPage, $resultsPerPage, $totalResults) {
        $totalPages = self::totalPagesFromResultCounts($resultsPerPage, $totalResults);
        return $this->calculatePageNumbers($currentPage, $totalPages);
    }

    public function calculatePageNumbers($currentPage, $totalPages) {
        if ($totalPages === 0) {
            return [];
        }
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages + 1;
            $showCurrent = false;
        }
        return [
            'first' => ArrayUtils::rangeAscending(1, min($this->firstPagesNumber, $currentPage - $this->leftPagesNumber - 1)),
            'left' => ArrayUtils::rangeAscending(max(1, $currentPage - $this->leftPagesNumber), $currentPage - 1),
            'right' => ArrayUtils::rangeAscending($currentPage + 1, min($currentPage + $this->rightPagesNumber, $totalPages)),
            'last' => ArrayUtils::rangeAscending(
                max($currentPage + $this->rightPagesNumber + 1, $totalPages - $this->lastPagesNumber + 1),
                $totalPages
            ),
            'leftEllipsis' => $this->firstPagesNumber + $this->leftPagesNumber + 1 < $currentPage,
            'rightEllipsis' => $currentPage + $this->rightPagesNumber < $totalPages - $this->lastPagesNumber,
            'previous' => $currentPage > 1 ? $currentPage - 1 : '',
            'next' => $currentPage < $totalPages ? $currentPage + 1 : '',
            'current' => $currentPage,
            'total' => $totalPages,
            'showCurrent' => $showCurrent ?? true,
        ];
    }
}
