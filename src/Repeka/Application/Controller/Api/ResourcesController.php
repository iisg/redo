<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceFileQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        $sortByMetadataIds = $request->query->get('sortByIds', []);
        Assertion::isArray($sortByMetadataIds);
        Assertion::isArray($resourceClasses);
        Assertion::isArray($resourceKindIds);
        $sortByMetadataIds = array_map(function ($sortBy) {
            return ['metadataId' => intval($sortBy['metadataId']), 'direction' => $sortBy['direction']];
        }, $sortByMetadataIds);
        $parentId = $request->query->get('parentId', 0);
        $topLevel = $request->query->get('topLevel', false);
        $resourceKinds = array_map(function ($resourceKindId) {
            return $this->handleCommand(new ResourceKindQuery($resourceKindId));
        }, $resourceKindIds);
        $contentsFilter = $request->get('contentsFilter', []);
        $resourceListQueryBuilder = ResourceListQuery::builder()
            ->filterByResourceClasses($resourceClasses)
            ->filterByResourceKinds($resourceKinds)
            ->filterByContents(is_array($contentsFilter) ? $contentsFilter : [])
            ->sortByMetadataIds($sortByMetadataIds);
        $page = $request->query->get('page', 1);
        if ($request->query->has('page')) {
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $resourceListQueryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        $resourceListQueryBuilder->filterByParentId($parentId);
        $resourceListQuery = $topLevel ? $resourceListQueryBuilder->onlyTopLevel()->build() : $resourceListQueryBuilder->build();
        /** @var PageResult $resources */
        $resources = $this->handleCommand($resourceListQuery);
        $response = $this->createJsonResponse($resources->getResults());
        $response->headers->set('pk_total', $resources->getTotalCount());
        $response->headers->set('pk_page', $page);
        return $response;
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(int $id) {
        $resource = $this->handleCommand(new ResourceQuery($id));
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
    public function postAction(Request $request, ResourceContents $resourceContents) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'kindId', 'kindId is missing');
        Assertion::numeric($data['kindId']);
        $resourceKind = $this->handleCommand(new ResourceKindQuery($data['kindId']));
        $command = new ResourceCreateCommand($resourceKind, $resourceContents);
        $resource = $this->handleCommand($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("POST")
     * POST instead of PUT, caused by multipart data
     * @see http://stackoverflow.com/questions/24385301/symfony-rest-file-upload-over-put-method
     */
    public function putAction(ResourceEntity $resource, ResourceContents $resourceContents) {
        $command = new ResourceUpdateContentsCommand($resource, $resourceContents);
        $resource = $this->handleCommand($command);
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route("/{resource}")
     * @Method("PATCH")
     */
    public function patchAction(ResourceEntity $resource, Request $request) {
        $data = $request->request->all();
        if (!empty($data['transitionId'])) {
            $command = new ResourceTransitionCommand($resource, $data['transitionId'], $this->getUser());
            $resource = $this->handleCommand($command);
            return $this->createJsonResponse($resource);
        }
        throw new BadRequestHttpException('Unsupported operation.');
    }

    /**
     * @Route("/{resource}")
     * @Method("DELETE")
     */
    public function deleteAction(ResourceEntity $resource) {
        $this->handleCommand(new ResourceDeleteCommand($resource));
        return new Response('', 204);
    }

    /**
     * @Route("/{resource}/files/{filename}")
     * @Method("GET")
     */
    public function downloadFileAction(ResourceEntity $resource, string $filename) {
        $filePath = $this->handleCommand(new ResourceFileQuery($resource, $filename));
        return new BinaryFileResponse($filePath);
    }
}
