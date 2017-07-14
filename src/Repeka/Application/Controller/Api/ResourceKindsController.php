<?php

namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Repository\ResourceWorkflowRepository;
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
    /** @var ResourceWorkflowRepository */
    private $resourceWorkflowRepository;

    public function __construct(ResourceWorkflowRepository $resourceWorkflowRepository) {
        $this->resourceWorkflowRepository = $resourceWorkflowRepository;
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $includeSystemResourceKinds = !!$request->query->get('systemResourceKind');
        $resourceKindList = $this->handleCommand(new ResourceKindListQuery($includeSystemResourceKinds));
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
            isset($data['workflowId']) ? $this->resourceWorkflowRepository->findOne($data['workflowId']) : null
        );
        $resourceKind = $this->handleCommand($command);
        return $this->createJsonResponse($resourceKind, 201);
    }

    /**
     * @Route("/{id}")
     * @Method("PATCH")
     */
    public function patchAction(int $id, Request $request) {
        $data = $request->request->all();
        $command = new ResourceKindUpdateCommand($id, $data['label'], $data['metadataList']);
        $resourceKind = $this->handleCommand($command);
        return $this->createJsonResponse($resourceKind);
    }
}
