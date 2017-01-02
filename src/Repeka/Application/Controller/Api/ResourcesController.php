<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resources")
 */
class ResourcesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $resources = $this->commandBus->handle(new ResourceListQuery());
        return $this->createJsonResponse($resources);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_STATIC_RESOURCES')")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        if (empty($data['kind_id'])) {
            throw $this->createNotFoundException();
        }
        $resourceKind = $this->container->get('repository.resource_kind')->findOne($data['kind_id']);
        $command = new ResourceCreateCommand($resourceKind, $data['contents'] ?? []);
        $resource = $this->commandBus->handle($command);
        return $this->createJsonResponse($resource, 201);
    }
}
