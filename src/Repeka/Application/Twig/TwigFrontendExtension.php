<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Utils\PrintableArray;

/**
 * All Twig extensions that are not strictly connected to display strategies, but helps to achieve specific tasks in frontend.
 */
class TwigFrontendExtension extends \Twig_Extension {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('resourceKind', [$this, 'fetchResourceKind']),
            new \Twig_Function('ftsFacetFilterParam', [$this, 'ftsFacetFilterParam']),
            new \Twig_Function('isFilteringByFacet', [$this, 'isFilteringByFacet']),
            new \Twig_Function('icon', [$this, 'icon']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('ftsContentsToResource', [$this, 'ftsContentsToResource']),
            new \Twig_Filter('sum', [$this, 'sumIterable']),
        ];
    }

    public function sumIterable($iterable) {
        if ($iterable instanceof PrintableArray) {
            $iterable = $iterable->flatten();
        }
        if (!is_array($iterable)) {
            $iterable = iterator_to_array($iterable);
        }
        $iterable = array_map(
            function ($value) {
                return is_numeric($value) ? $value : strval($value);
            },
            $iterable
        );
        return array_sum($iterable);
    }

    /**
     * Its aim is to transform hits from elasticsearch to look like a resource contents.
     * e.g. {2: [{value_text: AAA}]} into {2: [{value: AAA}]}
     * @param array $contents
     * @return ResourceContents
     */
    public function ftsContentsToResource(array $contents): ResourceContents {
        return ResourceContents::fromArray(
            $contents,
            function ($hit) {
                if (isset($hit['submetadata'])) {
                    unset($hit['submetadata']);
                }
                return current($hit);
            }
        );
    }

    public function fetchResourceKind($id) {
        return $this->resourceKindRepository->findOne($id);
    }

    public function ftsFacetFilterParam(string $aggregationName, $filterValue, array $currentFilters): array {
        $currentFilterValues = $this->getCurrentFacetFilters($aggregationName, $currentFilters);
        if (in_array($filterValue, $currentFilterValues)) {
            $currentFilterValues = array_diff($currentFilterValues, [$filterValue]);
        } else {
            $currentFilterValues[] = $filterValue;
        }
        sort($currentFilterValues);
        $filterValue = implode(',', $currentFilterValues);
        $currentFilters[$aggregationName] = $filterValue;
        return array_filter($currentFilters);
    }

    public function isFilteringByFacet(string $aggregationName, $filterValue, array $currentFilters): bool {
        return in_array($filterValue, $this->getCurrentFacetFilters($aggregationName, $currentFilters));
    }

    public function icon(string $name, string $size = '1'): \Twig_Markup {
        $iconTemplate = <<<ICON
<span class="icon" size="$size">
    <svg viewBox="0 0 1 1">
        <use xlink:href="/files/icons.svg#$name"></use>
    </svg>
</span>
ICON;
        return new \Twig_Markup($iconTemplate, 'UTF-8');
    }

    private function getCurrentFacetFilters(string $aggregationName, array $currentFilters): array {
        if (isset($currentFilters[$aggregationName])) {
            $currentFilterValues = explode(',', $currentFilters[$aggregationName]);
            return array_filter(array_filter($currentFilterValues, 'trim'), 'is_numeric');
        } else {
            return [];
        }
    }
}
