<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource-kinds")
 */
class ResourceKindsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $includeSystemResourceKinds = !!$request->query->get('systemResourceKind');
        $resourceKindList = $this->commandBus->handle(new ResourceKindListQuery($includeSystemResourceKinds));
        return $this->createJsonResponse($resourceKindList);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        $command = new ResourceKindCreateCommand(
            $data['label'] ?? [],
            $data['metadataList'] ?? [],
            isset($data['workflowId']) ? $this->get('repository.workflow')->findOne($data['workflowId']) : null
        );
        $resourceKind = $this->commandBus->handle($command);
        return $this->createJsonResponse($resourceKind, 201);
    }

    /**
     * @Route("/{id}")
     * @Method("PATCH")
     */
    public function patchAction(int $id, Request $request) {
        $data = $request->request->all();
        $command = new ResourceKindUpdateCommand($id, $data['label'], $data['metadataList']);
        $resourceKind = $this->commandBus->handle($command);
        return $this->createJsonResponse($resourceKind);
    }
}
