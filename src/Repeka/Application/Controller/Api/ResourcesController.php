<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQuery;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceFileQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $includeSystemResources = !!$request->query->get('systemResourceKind');
        $resources = $this->handleCommand(new ResourceListQuery($includeSystemResources));
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
        $resource = $this->handleCommand(new ResourceChildrenQuery($id == 0 ? null : $id));
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route
     * @Method("POST")
     * @ParamConverter("resourceContents", converter="Repeka\Application\ParamConverter\ResourceContentsParamConverter")
     */
    public function postAction(Request $request, array $resourceContents) {
        $data = $request->request->all();
        if (empty($data['kind_id'])) {
            throw new BadRequestHttpException('kind_id missing');
        }
        $resourceKind = $this->resourceKindRepository->findOne($data['kind_id']);
        $command = new ResourceCreateCommand($resourceKind, $resourceContents);
        $resource = $this->handleCommand($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("POST")
     * @ParamConverter("resourceContents", converter="Repeka\Application\ParamConverter\ResourceContentsParamConverter")
     * POST instead of PUT, caused by multipart data
     * @see http://stackoverflow.com/questions/24385301/symfony-rest-file-upload-over-put-method
     */
    public function putAction(ResourceEntity $resource, array $resourceContents) {
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
