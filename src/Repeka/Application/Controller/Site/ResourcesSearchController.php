<?php
namespace Repeka\Application\Controller\Site;

use Assert\Assertion;
use Elastica\ResultSet;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResourcesSearchController extends Controller {
    use CommandBusAware;

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function searchResourcesAction(
        string $template,
        array $ftsConfig,
        array $headers,
        Request $request
    ) {
        $phrase = $request->get('phrase');
        $filterableMetadataNamesOrIds = $ftsConfig['filterable_metadata_ids'] ?? [];
        $filterableMetadata = array_map([$this->metadataRepository, 'findByNameOrId'], $filterableMetadataNamesOrIds);
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
            $results = $this->fetchSearchResults($ftsConfig, $request, $metadataFilters, $phrase);
            $responseData['results'] = $results;
        }
        $response = $this->render($template, $responseData);
        $response->headers->add($headers);
        return $response;
    }

    private function fetchSearchResults(array $ftsConfig, Request $request, array $metadataFilters, string $phrase): ResultSet {
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
            ->setPhrase($phrase)
            ->setSearchableMetadata($searchableMetadata)
            ->setResourceClasses($ftsConfig['searchable_resource_classes'] ?? [])
            ->setResourceKindFacet(in_array(FtsConstants::KIND_ID, $facets))
            ->setMetadataFacets(array_diff($facets, [FtsConstants::KIND_ID]))
            ->setFacetsFilters($facetsFilters)
            ->setMetadataFilters($metadataFilters)
            ->build();
        /** @var ResultSet $results */
        $results = $this->handleCommand($query);
        return $results;
    }
}
