<?php
namespace Repeka\Application\Controller\Site;

use Assert\Assertion;
use Elastica\ResultSet;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Twig\Paginator;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResourcesSearchController extends Controller {
    use CommandBusAware;

    /** @var MetadataRepository */
    private $metadataRepository;
    private $paginator;
    private $resultsPerPage = 10;

    public function __construct(MetadataRepository $metadataRepository, Paginator $paginator) {
        $this->metadataRepository = $metadataRepository;
        $this->paginator = $paginator;
    }

    public function searchResourcesAction(
        string $template,
        array $ftsConfig,
        array $headers,
        Request $request
    ) {
        if (!in_array('text/html', $request->getAcceptableContentTypes())) {
            throw $this->createNotFoundException();
        }
        $phrase = $request->query->get('phrase');
        $filterableMetadataNamesOrIds = $ftsConfig['filterable_metadata_ids'] ?? [];
        $filterableMetadata = array_map([$this->metadataRepository, 'findByNameOrId'], $filterableMetadataNamesOrIds);
        $request->getSession()->set('search.phrase', $phrase);
        $responseData = [
            'phrase' => $phrase,
            'filterableMetadataList' => $filterableMetadata,
            'searchableResourceClasses' => $ftsConfig['searchable_resource_classes'] ?? [],
        ];
        if ($metadataFilters = array_filter($request->get('metadataFilters', []))) {
            $filterableMetadataIds = EntityUtils::mapToIds($filterableMetadata);
            $metadataFilters = array_intersect_key($metadataFilters, array_combine($filterableMetadataIds, $filterableMetadataIds));
        }
        if ($phrase || $metadataFilters) {
            $page = intval($request->get('page', 1));
            if ($page < 1) {
                $page = 1;
            }
            $results = $this->fetchSearchResults($ftsConfig, $request, $metadataFilters, $phrase, $page);
            $responseData['results'] = $results;
            $pagination = $this->paginator->paginate($page, $this->resultsPerPage, $results->getTotalHits());
            $responseData['pagination'] = $pagination;
        }
        $response = $this->render($template, $responseData);
        $response->headers->add($headers);
        return $response;
    }

    private function fetchSearchResults(array $ftsConfig, Request $request, array $metadataFilters, ?string $phrase, int $page): ResultSet {
        $facetsFilters = array_map(
            function ($filter) {
                return explode(',', $filter);
            },
            (array)$request->get('facetFilters')
        );
        $searchableMetadata = $ftsConfig['searchable_metadata_ids'] ?? [];
        if ($metadataSubset = $request->get('metadataSubset', '')) {
            $searchableMetadata = array_intersect($searchableMetadata, explode(',', $metadataSubset));
        }
        Assertion::notEmpty($searchableMetadata, 'Query must include some metadata');
        $facets = $ftsConfig['facets'] ?? [];
        $query = ResourceListFtsQuery::builder()
            ->setPhrase($phrase ?: '')
            ->setSearchableMetadata($searchableMetadata)
            ->setResourceClasses($ftsConfig['searchable_resource_classes'] ?? [])
            ->setResourceKindFacet(in_array(FtsConstants::KIND_ID, $facets))
            ->setMetadataFacets(array_diff($facets, [FtsConstants::KIND_ID]))
            ->setFacetsFilters($facetsFilters)
            ->setMetadataFilters($metadataFilters)
            ->setPage($page)
            ->setResultsPerPage($this->resultsPerPage)
            ->build();
        /** @var ResultSet $results */
        $results = $this->handleCommand($query);
        return $results;
    }
}
