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

    public function paginate($current, $total) {
        if ($total === 0) {
            return [];
        }
        if ($current > $total) {
            $current = $total + 1;
            $showCurrent = false;
        }
        return [
            'first' => ArrayUtils::rangeAscending(1, min($this->firstPagesNumber, $current - $this->leftPagesNumber - 1)),
            'left' => ArrayUtils::rangeAscending(max(1, $current - $this->leftPagesNumber), $current - 1),
            'right' => ArrayUtils::rangeAscending($current + 1, min($current + $this->rightPagesNumber, $total)),
            'last' => ArrayUtils::rangeAscending(max($current + $this->rightPagesNumber + 1, $total - $this->lastPagesNumber + 1), $total),
            'leftEllipsis' => $this->firstPagesNumber + $this->leftPagesNumber + 1 < $current,
            'rightEllipsis' => $current + $this->rightPagesNumber < $total - $this->lastPagesNumber,
            'previous' => $current > 1 ? $current - 1 : '',
            'next' => $current < $total ? $current + 1 : '',
            'current' => $current,
            'total' => $total,
            'showCurrent' => $showCurrent ?? true,
        ];
    }
}
