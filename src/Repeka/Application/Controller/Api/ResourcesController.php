<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
    public function getListAction() {
        $resources = $this->handle(new ResourceListQuery());
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
     * @Security("has_role('ROLE_STATIC_RESOURCES')")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        if (empty($data['kind_id'])) {
            throw new BadRequestHttpException('kind_id missing');
        }
        $resourceKind = $this->container->get('repository.resource_kind')->findOne($data['kind_id']);
        $command = new ResourceCreateCommand($resourceKind, $data['contents'] ?? []);
        $resource = $this->handle($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{resource}")
     * @Method("PUT")
     * @Security("has_role('ROLE_STATIC_RESOURCES')")
     */
    public function putAction(ResourceEntity $resource, Request $request) {
        $data = $request->request->all();
        $command = new ResourceUpdateContentsCommand($resource, $data['contents'] ?? []);
        $resource = $this->handle($command);
        return $this->createJsonResponse($resource);
    }
}
