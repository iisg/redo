<?php
namespace Repeka\Domain\UseCase;

class ColumnSortDataConverter {
    public function convertSortByMetadataColumnsToIntegers(array $sortByIds): array {
        return array_map(
            function ($sortBy) {
                $sortId = is_numeric($sortBy['columnId']) ? intval($sortBy['columnId']) : $sortBy['columnId'];
                return ['columnId' => $sortId, 'direction' => $sortBy['direction'], 'language' => $sortBy['language'] ?? ''];
            },
            $sortByIds
        );
    }
}
