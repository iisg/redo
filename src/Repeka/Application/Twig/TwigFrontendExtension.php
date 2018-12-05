<?php
namespace Repeka\Application\Twig;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Utils\PrintableArray;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * All Twig extensions that are not strictly connected to display strategies, but helps to achieve specific tasks in frontend.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TwigFrontendExtension extends \Twig_Extension {
    use CommandBusAware;

    /** @var string */
    private $currentUri;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var Paginator */
    private $paginator;
    /** @var FrontendConfig */
    private $frontendConfig;

    public function __construct(
        RequestStack $requestStack,
        ResourceKindRepository $resourceKindRepository,
        Paginator $paginator,
        FrontendConfig $frontendConfig
    ) {
        $request = $requestStack->getCurrentRequest();
        $this->currentUri = $request ? $request->getRequestUri() : null;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->paginator = $paginator;
        $this->frontendConfig = $frontendConfig;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('resourceKind', [$this, 'fetchResourceKind']),
            new \Twig_Function('metadataGroup', [$this, 'getMetadataGroup']),
            new \Twig_Function('ftsFacetFilterParam', [$this, 'ftsFacetFilterParam']),
            new \Twig_Function('isFilteringByFacet', [$this, 'isFilteringByFacet']),
            new \Twig_Function('icon', [$this, 'icon']),
            new \Twig_Function('resources', [$this, 'fetchResources']),
            new \Twig_Function('urlMatches', [$this, 'urlMatches']),
            new \Twig_Function('paginate', [$this->paginator, 'paginate']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('ftsContentsToResource', [$this, 'ftsContentsToResource']),
            new \Twig_Filter('sum', [$this, 'sumIterable']),
            new \Twig_Filter('bibtexEscape', [$this, 'bibtexEscape']),
            new \Twig_Filter('childrenAllowed', [$this, 'resourceCanHaveChildren']),
            new \Twig_Filter('wrap', [$this, 'wrap']),
            new \Twig_Filter('basename', [$this, 'basename']),
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

    public function getMetadataGroup($metadataGroupId): array {
        $lookup = EntityUtils::getLookupMap($this->frontendConfig->getConfig()['metadata_groups']);
        return $lookup[$metadataGroupId] ?? [];
    }

    public function getMetadataGroupsDetails() {
        return $this->frontendConfig->getConfig()['metadata_groups'];
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

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    public function fetchResources(array $filters): iterable {
        $builder = ResourceListQuery::builder();
        if (array_key_exists('parentId', $filters)) {
            if ($filters['parentId']) {
                $builder->filterByParentId($filters['parentId']);
            } else {
                $builder->onlyTopLevel();
            }
        }
        if (isset($filters['resourceClass'])) {
            $filters['resourceClasses'] = [$filters['resourceClass']];
        }
        if (isset($filters['resourceClasses']) && $filters['resourceClasses']) {
            $builder->filterByResourceClasses($filters['resourceClasses']);
        }
        if (isset($filters['resourceKindIds']) && $filters['resourceKindIds']) {
            $builder->filterByResourceKinds($filters['resourceKindIds']);
        }
        if (isset($filters['contentsFilter']) && is_array($filters['contentsFilter'])) {
            $builder->filterByContents($filters['contentsFilter']);
        }
        if (isset($filters['resultsPerPage']) && $filters['resultsPerPage']) {
            $builder->setPage(1);
            $builder->setResultsPerPage($filters['resultsPerPage']);
        }
        if (isset($filters['page']) && $filters['page']) {
            $builder->setPage($filters['page']);
        }
        if (isset($filters['sortBy']) && is_array($filters['sortBy'])) {
            $builder->sortBy([$filters['sortBy']]);
        }
        return FirewallMiddleware::bypass(
            function () use ($builder) {
                return $this->handleCommand($builder->build());
            }
        );
    }

    public function bibtexEscape($value) {
        return '"' . addcslashes($value, '{}"$\\') . '"';
    }

    public function urlMatches(string ...$urls): bool {
        foreach ($urls as $url) {
            $urlLength = strlen($url);
            if (substr($url, $urlLength - 1) == '#') {
                if ($this->currentUri == substr($url, 0, $urlLength - 1)) {
                    return true;
                }
            } elseif (substr($this->currentUri, 0, $urlLength) == $url) {
                return true;
            }
        }
        return false;
    }

    public function resourceCanHaveChildren(ResourceEntity $resource): bool {
        $parentMetadata = $resource->getKind()->getMetadataById(SystemMetadata::PARENT);
        $constraints = $parentMetadata->getConstraints();
        return array_key_exists('resourceKind', $constraints) && count($constraints['resourceKind']) > 0;
    }

    public function wrap($value, $prefix, $suffix = null) {
        if (is_numeric($value)) {
            $value = strval($value);
        }
        if (is_string($value)) {
            return $prefix . $value . $suffix;
        } elseif (is_array($value)) {
            return array_map(
                function ($element) use ($prefix, $suffix) {
                    return $this->wrap($element, $prefix, $suffix);
                },
                $value
            );
        } else {
            throw new \InvalidArgumentException('Unsupported value for wrap filter: ' . gettype($value));
        }
    }

    public function basename(string $value) {
        $parts = preg_split('#[\\\/]#', $value);
        return $parts[count($parts) - 1];
    }
}
