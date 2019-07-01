<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceCloneManyTimesCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\TreeResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/resources")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourcesController extends ApiController {
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $evaluator) {
        $this->displayStrategyEvaluator = $evaluator;
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $resourceListQuery = $this->getResourceListQueryBuilder($request)->build();
        /** @var PageResult $resources */
        $resources = $this->handleCommand($resourceListQuery);
        return $this->createPageResponse($resources);
    }

    /**
     * @Route("/teasers")
     * @Method("GET")
     */
    public function getTeasersAction(Request $request) {
        $query = $this->getResourceListQueryBuilder($request)
            ->setPermissionMetadataId(SystemMetadata::TEASER_VISIBILITY)
            ->build();
        $resources = $this->handleCommand($query);
        return $this->createPageResponse($resources, Response::HTTP_OK, [ResourceNormalizer::ALWAYS_RETURN_TEASER]);
    }

    protected function getResourceListQueryBuilder(Request $request): ResourceListQueryBuilder {
        $resourceClasses = $request->get('resourceClasses', []);
        $resourceKindIds = $request->get('resourceKinds', []);
        $sortByIds = $request->query->get('sortByIds', []);
        $workflowPlacesIds = $request->query->get('workflowPlacesIds', []);
        $contentsFilter = $request->get('contentsFilter', []);
        $parentId = $request->query->get('parentId', 0);
        $topLevel = $request->query->get('topLevel', false);
        $ids = array_values(array_filter(array_map('trim', explode(',', $request->query->get('ids', '')))));
        Assertion::isArray($resourceClasses);
        Assertion::isArray($resourceKindIds);
        Assertion::isArray($sortByIds);
        Assertion::isArray($workflowPlacesIds);
        $resourceListQueryBuilder = ResourceListQuery::builder()
            ->filterByResourceClasses($resourceClasses)
            ->filterByResourceKinds($resourceKindIds)
            ->filterByContents(is_array($contentsFilter) ? $contentsFilter : [])
            ->sortBy($sortByIds)
            ->filterByParentId($parentId)
            ->filterByIds($ids)
            ->filterByWorkflowPlacesIds($workflowPlacesIds);
        if ($topLevel) {
            $resourceListQueryBuilder->onlyTopLevel();
        }
        if ($request->query->has('page')) {
            $page = $request->query->get('page', 1);
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $resourceListQueryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        return $resourceListQueryBuilder;
    }

    /**
     * @Route("/tree")
     * @Method("GET")
     */
    public function getTreeAction(Request $request) {
        $rootId = $request->query->get('rootId', 0);
        $depth = $request->query->get('depth', 0);
        $siblings = $request->query->get('siblings', 5);
        $resourceClasses = $request->get('resourceClasses', []);
        $resourceKindIds = $request->get('resourceKinds', []);
        $contentsFilter = $request->get('contentsFilter', []);
        Assertion::isArray($resourceClasses);
        Assertion::isArray($resourceKindIds);
        Assertion::isArray($contentsFilter);
        $resourceTreeQueryBuilder = ResourceTreeQuery::builder()
            ->forRootId($rootId)
            ->includeWithinDepth($depth)
            ->setSiblings($siblings)
            ->filterByResourceClasses($resourceClasses)
            ->filterByResourceKinds($resourceKindIds)
            ->filterByContents($contentsFilter);
        if ($request->query->has('page')) {
            $page = $request->query->get('page', 1);
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $resourceTreeQueryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        if ($request->query->has('oneMoreElements')) {
            $resourceTreeQueryBuilder->oneMoreElements();
        }
        $resourceTreeQuery = $resourceTreeQueryBuilder->build();
        /** @var TreeResult $resources */
        $resources = $this->handleCommand($resourceTreeQuery);
        $json = $this->createJsonResponse($resources, Response::HTTP_OK, [ResourceNormalizer::ALWAYS_RETURN_TEASER]);
        return $json;
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function getAction(ResourceEntity $resource, Request $request) {
        if ($resource->isDisplayStrategiesDirty() || $request->headers->has('EvaluateDisplayStrategies')) {
            $resource = $this->handleCommand(new ResourceEvaluateDisplayStrategiesCommand($resource));
        }
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route("/{id}/hierarchy")
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function getHierarchy(ResourceEntity $resource) {
        $path = $this->handleCommand(new ResourceTopLevelPathQuery($resource, SystemMetadata::PARENT));
        return $this->createJsonResponse($path, Response::HTTP_OK, [ResourceNormalizer::ALWAYS_RETURN_TEASER]);
    }

    /**
     * @Route("/{id}/evaluate-display-strategy")
     * @Method("PATCH")
     */
    public function evaluateDisplayStrategy(string $id, Request $request) {
        $resource = $this->handleCommand(new ResourceQuery(intval($id)));
        $data = $request->request->all();
        Assertion::keyExists($data, 'template');
        $result = $this->displayStrategyEvaluator->render($resource, $data['template']);
        return $this->createJsonResponse(['result' => $result]);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        if (isset($data['id']) && is_numeric($data['id']) && is_numeric($data['cloneTimes'])) {
            $resourceToClone = $this->handleCommand(new ResourceQuery($data['id']));
            $createCommand = new ResourceCloneManyTimesCommand($resourceToClone, intval($data['cloneTimes']));
        } else {
            Assertion::keyExists($data, 'kindId', 'kindId is missing');
            Assertion::keyExists($data, 'contents', 'contents are missing');
            Assertion::numeric($data['kindId']);
            $resourceKind = $this->handleCommand(new ResourceKindQuery($data['kindId']));
            $resourceContents = ResourceContents::fromArray($data['contents']);
            $createCommand = new ResourceCreateCommand($resourceKind, $resourceContents);
        }
        $resource = $this->handleCommand($createCommand);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("PUT")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function putAction(Request $request, ResourceEntity $resource) {
        $requestData = $request->request->all();
        Assertion::keyExists($requestData, 'contents', 'contents are missing');
        $resourceContents = ResourceContents::fromArray($requestData['contents']);
        $godEdit = $request->headers->get('god-edit');
        if ($godEdit) {
            $kindId = $request->get('newKindId');
            $placesIds = $request->get('placesIds', []);
            $command = ResourceGodUpdateCommand::builder()
                ->setResource($resource)
                ->setNewContents($resourceContents)
                ->changeResourceKind($kindId)
                ->changePlaces($placesIds)
                ->build();
            $resource = $this->handleCommand($command);
        } else {
            $transitionId = $request->get('transitionId', '');
            $command = ($transitionId === '')
                ? new ResourceUpdateContentsCommand($resource, $resourceContents, $this->getUser())
                : new ResourceTransitionCommand($resource, $resourceContents, $transitionId, $this->getUser());
            $resource = $this->handleCommand($command);
        }
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route("/{resource}")
     * @Method("DELETE")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function deleteAction(ResourceEntity $resource) {
        $this->handleCommand(new ResourceDeleteCommand($resource));
        return new Response('', 204);
    }
}
