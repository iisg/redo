<?php
namespace Repeka\Plugins\Redo\Controller;

use Assert\Assertion;
use Elastica\ResultSet;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Twig\Paginator;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RedoFtsSearchController extends Controller {
    use CommandBusAware;

    /** @var MetadataRepository */
    private $metadataRepository;
    private $paginator;
    private $resultsPerPage = 10;
    /** @var array */
    private $ftsConfig;

    public function __construct(array $ftsConfig, MetadataRepository $metadataRepository, Paginator $paginator) {
        $this->ftsConfig = $ftsConfig;
        $this->metadataRepository = $metadataRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/")
     * @Template("redo/home/home.twig")
     */
    public function homeAction(Request $request) {
        return $this->buildFtsResponseData($request);
    }

    /**
     * @Route("/search")
     * @Template("redo/search/search-results.twig")
     */
    public function searchResourcesAction(Request $request) {
        $responseData = $this->buildFtsResponseData($request);
        if ($metadataFilters = array_filter($request->get('metadataFilters', []))) {
            $filterableMetadataIds = EntityUtils::mapToIds($responseData['filterableMetadataList']);
            $metadataFilters = array_intersect_key($metadataFilters, array_combine($filterableMetadataIds, $filterableMetadataIds));
        }
        $page = intval($request->get('page', 1));
        if ($page < 1) {
            $page = 1;
        }
        $results = $this->fetchSearchResults($request, $metadataFilters, $responseData['phrase'], $page);
        $responseData['results'] = $results;
        $pagination = $this->paginator->paginate($page, $this->resultsPerPage, $results->getTotalHits());
        $responseData['pagination'] = $pagination;
        return $responseData;
    }

    private function buildFtsResponseData(Request $request): array {
        $phrase = $request->query->get('phrase', '');
        $filterableMetadataNamesOrIds = $this->ftsConfig['filterable_metadata_ids'] ?? [];
        $filterableMetadata = array_map([$this->metadataRepository, 'findByNameOrId'], $filterableMetadataNamesOrIds);
        return [
            'phrase' => $phrase,
            'filterableMetadataList' => $filterableMetadata,
            'searchableResourceClasses' => $this->ftsConfig['searchable_resource_classes'] ?? [],
        ];
    }

    private function fetchSearchResults(Request $request, array $metadataFilters, ?string $phrase, int $page): ResultSet {
        $facetsFilters = array_map(
            function ($filter) {
                return explode(',', $filter);
            },
            (array)$request->get('facetFilters')
        );
        $searchableMetadata = $this->ftsConfig['searchable_metadata_ids'] ?? [];
        Assertion::notEmpty($searchableMetadata, 'Query must include some metadata');
        $facets = $this->ftsConfig['facets'] ?? [];
        $query = ResourceListFtsQuery::builder()
            ->setPhrase($phrase ?: '')
            ->setSearchableMetadata($searchableMetadata)
            ->setResourceClasses($this->ftsConfig['searchable_resource_classes'] ?? [])
            ->setResourceKindFacet(in_array(FtsConstants::KIND_ID, $facets))
            ->setMetadataFacets(array_diff($facets, [FtsConstants::KIND_ID]))
            ->setFacetsFilters($facetsFilters)
            ->setMetadataFilters($metadataFilters)
            ->setOnlyTopLevel(!$metadataFilters && !$phrase)
            ->setPage($page)
            ->setResultsPerPage($this->resultsPerPage)
            ->build();
        /** @var ResultSet $results */
        $results = $this->handleCommand($query);
        return $results;
    }
}
