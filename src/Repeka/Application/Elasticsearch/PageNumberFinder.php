<?php
namespace Repeka\Application\Elasticsearch;

use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceFileStorage;

class PageNumberFinder {

    /** @var ResourceFileStorage */
    private $fileStorage;

    const HIGHLIGHT = 'highlight';
    const PAGE_NUMBER = 'page_number';

    public function __construct(ResourceFileStorage $fileStorage) {
        $this->fileStorage = $fileStorage;
    }

    public function matchSearchHitsWithPageNumbers(ResourceEntity $resource, string $control, array $paths, array $highlights): array {
        if ($control == MetadataControl::DIRECTORY) {
            $paths = $this->getFilesFromDirectory($resource, $paths);
        }
        $highlights = $this->chooseFragmentWithHit($highlights);
        $hits = $this->clearHighlight($highlights);
        $results = [];
        foreach ($this->onlySupportedFileFormats($paths) as $file) {
            $pages = explode("\f", $this->fileStorage->getFileContents($resource, $file));
            $results = array_merge(
                $results,
                $this->findPageNumbersInSingleFile($pages, $highlights, $hits, count($results))
            );
            if (count($results) == count($highlights)) {
                return $results;
            }
        }
        return [];
    }

    private function getFilesFromDirectory(ResourceEntity $resource, array $paths) {
        $allMetadataFiles = [];
        foreach ($paths as $path) {
            $files = $this->fileStorage->getDirectoryContents($resource, $path);
            $allMetadataFiles = array_merge($allMetadataFiles, $files);
        }
        return $allMetadataFiles;
    }

    private function findPageNumbersInSingleFile(
        array $pages,
        array $highlights,
        array $hits,
        int $nextHitIndex
    ): array {
        $pageNumber = 1;
        $page = $pages[$pageNumber - 1] ?? '';
        $results = [];
        $hit = $hits[$nextHitIndex] ?? null;
        $numberOfPages = count($pages);
        $numberOfHits = count($hits);
        while ($nextHitIndex < $numberOfHits && $pageNumber <= $numberOfPages) {
            $hitPos = 0;
            while ($hitPos = (strpos($page, $hit, $hitPos)) !== false) {
                $results[$nextHitIndex] = [
                    self::PAGE_NUMBER => $pageNumber,
                    self::HIGHLIGHT => $highlights[$nextHitIndex],
                ];
                $hit = $hits[++$nextHitIndex] ?? null;
            }
            $page = $pages[$pageNumber] ?? '';
            $pageNumber++;
        }
        return $results;
    }

    private function chooseFragmentWithHit(array $hits): array {
        return array_map(
            function ($hit) {
                $tokens = explode("\f", $hit);
                foreach ($tokens as $token) {
                    if (strpos($token, "<em>") !== false) {
                        return $token;
                    }
                }
                return $tokens[0];
            },
            $hits
        );
    }

    private function clearHighlight(array $hits): array {
        return array_map(
            function ($hit) {
                return preg_replace('%</?em>%', '', $hit);
            },
            $hits
        );
    }

    private function onlySupportedFileFormats(array $files): array {
        return array_filter(
            $files,
            function ($file) {
                return in_array(pathinfo($file, PATHINFO_EXTENSION), FtsConstants::SUPPORTED_FILE_EXTENSIONS);
            }
        );
    }
}
