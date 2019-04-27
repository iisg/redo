<?php
namespace Repeka\Plugins\Redo\Controller;

use Assert\Assertion;
use Elastica\ResultSet;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Twig\Paginator;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\EndpointUsageLogRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class RedoFtsSearchController extends Controller {
    use CommandBusAware;

    /** @var MetadataRepository */
    private $metadataRepository;

    private $paginator;
    private $resultsPerPage = 10;
    /** @var array */
    private $ftsConfig;
    private $endpointUsageLogRepository;

    public function __construct(
        array $ftsConfig,
        MetadataRepository $metadataRepository,
        Paginator $paginator,
        EndpointUsageLogRepository $endpointUsageLogRepository
    ) {
        $this->ftsConfig = $ftsConfig;
        $this->metadataRepository = $metadataRepository;
        $this->paginator = $paginator;
        $this->endpointUsageLogRepository = $endpointUsageLogRepository;
    }

    /**
     * @Route("/")
     * @Template("redo/home/home.twig")
     */
    public function homeAction(Request $request) {
        if ($resourceId = $request->get('resourceId')) {
            return $this->redirect('/resources/' . $resourceId);
        }
        if ($suwId = $request->get('suwId')) {
            $query = ResourceListQuery::builder()->filterByContents(['suw_id' => $suwId])->build();
            $resources = $this->handleCommand($query);
            if (count($resources)) {
                return $this->redirect('/resources/' . $resources[0]->getId());
            }
        }
        $this->handleCommand(new EndpointUsageLogCreateCommand($request, 'home'));
        return [
            'filterableMetadataList' => $this->findFilterableMetadata(),
            'searchableResourceClasses' => $this->ftsConfig['searchable_resource_classes'] ?? [],
        ];
    }

    /**
     * @Route("/search")
     * @Template("redo/search/search-results.twig")
     */
    public function searchResourcesAction(Request $request) {
        $phrase = $request->query->get('phrase', '');
        $filterableMetadata = $this->findFilterableMetadata();
        $responseData = [
            'phrase' => $phrase,
            'filterableMetadataList' => $filterableMetadata,
            'searchableResourceClasses' => $this->ftsConfig['searchable_resource_classes'] ?? [],
        ];
        if ($metadataFilters = array_filter($request->get('metadataFilters', []))) {
            $filterableMetadataIds = EntityUtils::mapToIds($filterableMetadata);
            $metadataFilters = array_intersect_key($metadataFilters, array_combine($filterableMetadataIds, $filterableMetadataIds));
        }
        $page = intval($request->get('page', 1));
        if ($page < 1) {
            $page = 1;
        }
        $results = $this->fetchSearchResults($request, $metadataFilters, $phrase, $page);
        $responseData['results'] = $results;
        $pagination = $this->paginator->paginate($page, $this->resultsPerPage, $results->getTotalHits());
        $responseData['pagination'] = $pagination;
        return $responseData;
    }

    private function fetchSearchResults(Request $request, array $metadataFilters, ?string $phrase, int $page): ResultSet {
        $facetsFilters = $request->get('facetFilters', []);
        $searchableMetadata = $this->ftsConfig['searchable_metadata_ids'] ?? [];
        Assertion::notEmpty($searchableMetadata, 'Query must include some metadata');
        $facets = $this->ftsConfig['facets'] ?? [];
        $onlyTopLevel = false;
        if (!$metadataFilters && !$phrase) {
            try {
                $parentPathLengthMetadata = $this->metadataRepository->findByName('parent_path_length');
                $metadataFilters = [$parentPathLengthMetadata->getId() => [1]];
            } catch (EntityNotFoundException $e) {
                $onlyTopLevel = true;
            }
        }
        $metadataFilters = $this->adjustDateMetadataFilters($metadataFilters);
        $query = ResourceListFtsQuery::builder()
            ->setPhrase($phrase ?: '')
            ->setSearchableMetadata($searchableMetadata)
            ->setResourceClasses($this->ftsConfig['searchable_resource_classes'] ?? [])
            ->setResourceKindFacet(in_array(FtsConstants::KIND_ID, $facets))
            ->setMetadataFacets(array_diff($facets, [FtsConstants::KIND_ID]))
            ->setFacetsFilters($facetsFilters)
            ->setMetadataFilters($metadataFilters)
            ->setOnlyTopLevel($onlyTopLevel)
            ->setPage($page)
            ->setResultsPerPage($this->resultsPerPage)
            ->build();
        /** @var ResultSet $results */
        $results = $this->handleCommand($query);
        return $results;
    }

    private function adjustDateMetadataFilters(array $metadataFilters): array {
        foreach ($metadataFilters as &$metadataFilter) {
            if (array_key_exists('from', $metadataFilter) && array_key_exists('to', $metadataFilter)) {
                $metadataFilter['from'] = $metadataFilter['from'] ? $metadataFilter['from'] . '-01-01' : '';
                $metadataFilter['to'] = $metadataFilter['to'] ? $metadataFilter['to'] . '-12-31' : '';
            }
        }
        return $metadataFilters;
    }

    private function findFilterableMetadata(): array {
        $filterableMetadataNamesOrIds = $this->ftsConfig['filterable_metadata_ids'] ?? [];
        $searchableResourceClasses = $this->ftsConfig['searchable_resource_classes'] ?? [];
        $searchableResourceClasses[] = null;
        return array_map(
            function ($nameOrId) use ($searchableResourceClasses) {
                foreach ($searchableResourceClasses as $resourceClass) {
                    try {
                        return $this->metadataRepository->findByNameOrId($nameOrId, $resourceClass);
                    } catch (EntityNotFoundException $e) {
                    }
                }
                throw new \InvalidArgumentException('Invalid filterable metadata name or id: ' . $nameOrId);
            },
            $filterableMetadataNamesOrIds
        );
    }
}
