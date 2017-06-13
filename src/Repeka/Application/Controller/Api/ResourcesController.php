<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/resources")
 */
class ResourcesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $includeSystemResources = !!$request->query->get('systemResourceKind');
        $resources = $this->handle(new ResourceListQuery($includeSystemResources));
        return $this->createJsonResponse($resources);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(int $id) {
        $resource = $this->handle(new ResourceQuery($id));
        return $this->createJsonResponse($resource);
    }

    /**
     * @Route
     * @Method("POST")
     * @ParamConverter("resourceContents", converter="repeka.converter.resource_contents_param")
     */
    public function postAction(Request $request, array $resourceContents) {
        $data = $request->request->all();
        if (empty($data['kind_id'])) {
            throw new BadRequestHttpException('kind_id missing');
        }
        $resourceKind = $this->container->get('repository.resource_kind')->findOne($data['kind_id']);
        $command = new ResourceCreateCommand($resourceKind, $resourceContents);
        $resource = $this->handle($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("POST")
     * @ParamConverter("resourceContents", converter="repeka.converter.resource_contents_param")
     * POST instead of PUT, caused by multipart data
     * @see http://stackoverflow.com/questions/24385301/symfony-rest-file-upload-over-put-method
     */
    public function putAction(ResourceEntity $resource, array $resourceContents) {
        $command = new ResourceUpdateContentsCommand($resource, $resourceContents);
        $resource = $this->handle($command);
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
            $resource = $this->handle($command);
            return $this->createJsonResponse($resource);
        }
        throw new BadRequestHttpException('Unsupported operation.');
    }
}
