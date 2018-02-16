<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQuery;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceFileQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
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
        $resourceClasses = array_filter(explode(',', $request->query->get('resourceClasses', '')));
        $resourceKindIds = array_filter(explode(',', $request->query->get('resourceKinds', '')));
        $topLevel = $request->query->get('topLevel', false);
        $resourceKinds = array_map(function ($resourceKindId) {
            return $this->handleCommand(new ResourceKindQuery($resourceKindId));
        }, $resourceKindIds);
        $resourceListQueryBuilder = ResourceListQuery::builder()
            ->filterByResourceClasses($resourceClasses)
            ->filterByResourceKinds($resourceKinds);
        $resourceListQuery = $topLevel ? $resourceListQueryBuilder->onlyTopLevel()->build() : $resourceListQueryBuilder->build();
        $resources = $this->handleCommand($resourceListQuery);
        return $this->createJsonResponse($resources);
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
     * @Route("/{id}/resources")
     * @Method("GET")
     */
    public function getChildrenAction(int $id) {
        $resource = $this->handleCommand(new ResourceChildrenQuery($id));
        return $this->createJsonResponse($resource);
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
