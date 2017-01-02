<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource-kinds")
 */
class ResourceKindsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $resourceKindList = $this->commandBus->handle(new ResourceKindListQuery());
        return $this->createJsonResponse($resourceKindList);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_STATIC_RESOURCE_KINDS')")
     */
    public function postAction(Request $request) {
        $command = ResourceKindCreateCommand::fromArray($request->request->all());
        $resourceKind = $this->commandBus->handle($command);
        return $this->createJsonResponse($resourceKind, 201);
    }
}
