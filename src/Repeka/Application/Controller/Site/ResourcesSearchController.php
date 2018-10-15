<?php
namespace Repeka\Application\Controller\Site;

use Assert\Assertion;
use Elastica\ResultSet;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResourcesSearchController extends Controller {
    use CommandBusAware;

    public function searchResourcesAction(
        string $template,
        array $ftsConfig,
        array $headers,
        Request $request
    ) {
        $phrase = $request->get('phrase');
        $filters = array_map(
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
            ->setFacetsFilters($filters)
            ->build();
        /** @var ResultSet $results */
        $results = $this->handleCommand($query);
        $response = $this->render($template, ['results' => $results, 'phrase' => $phrase]);
        $response->headers->add($headers);
        return $response;
    }
}
