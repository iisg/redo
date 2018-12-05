<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\TreeResult;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/resources")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourcesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $resourceClasses = $request->get('resourceClasses', []);
        $resourceKindIds = $request->get('resourceKinds', []);
        $sortByIds = $request->query->get('sortByIds', []);
        $workflowPlacesIds = $request->query->get('workflowPlacesIds', []);
        $contentsFilter = $request->get('contentsFilter', []);
        $parentId = $request->query->get('parentId', 0);
        $topLevel = $request->query->get('topLevel', false);
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
            ->filterByWorkflowPlacesIds($workflowPlacesIds);
        if ($topLevel) {
            $resourceListQueryBuilder->onlyTopLevel();
        }
        if ($request->query->has('page')) {
            $page = $request->query->get('page', 1);
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $resourceListQueryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        $resourceListQuery = $resourceListQueryBuilder->build();
        /** @var PageResult $resources */
        $resources = $this->handleCommand($resourceListQuery);
        return $this->createPageResponse($resources);
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
     */
    public function getAction(string $id) {
        /** @var ResourceEntity $resource */
        $resource = $this->handleCommand(new ResourceQuery(intval($id)));
        if ($resource->isDisplayStrategiesDirty()) {
            $resource = $this->handleCommand(new ResourceEvaluateDisplayStrategiesCommand($resource));
        }
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route("/{id}/hierarchy")
     * @Method("GET")
     */
    public function getHierarchy(ResourceEntity $resource) {
        $path = $this->handleCommand(new ResourceTopLevelPathQuery($resource, SystemMetadata::PARENT));
        return $this->createJsonResponse($path);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'kindId', 'kindId is missing');
        Assertion::keyExists($data, 'contents', 'contents are missing');
        Assertion::numeric($data['kindId']);
        $resourceKind = $this->handleCommand(new ResourceKindQuery($data['kindId']));
        $resourceContents = ResourceContents::fromArray($data['contents']);
        $command = isset($data['id']) && is_numeric($data['id'])
            ? new ResourceCloneCommand($resourceKind, intval($data['id']), $resourceContents, $this->getUser())
            : new ResourceCreateCommand($resourceKind, $resourceContents, $this->getUser());
        $resource = $this->handleCommand($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("PUT")
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
     */
    public function deleteAction(ResourceEntity $resource) {
        $this->handleCommand(new ResourceDeleteCommand($resource));
        return new Response('', 204);
    }
}
