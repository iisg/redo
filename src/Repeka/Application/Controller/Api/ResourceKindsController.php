<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(int $id) {
        $resourceKind = $this->handleCommand(new ResourceKindQuery($id));
        return $this->createJsonResponse($resourceKind);
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $resourceClass = $request->query->get('resourceClass', '');
        if (empty($resourceClass)) {
            $resourceKindList = $this->handleCommand(new ResourceKindListQuery());
        } else {
            $resourceKindList = $this->handleCommand(new ResourceKindByResourceClassListQuery($resourceClass));
        }
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
            $data['displayStrategies'] ?? [],
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
        Assertion::keyExists($data, 'label');
        Assertion::keyExists($data, 'metadataList');
        Assertion::keyExists($data, 'displayStrategies');
        $command = new ResourceKindUpdateCommand($id, $data['label'], $data['metadataList'], $data['displayStrategies']);
        $resourceKind = $this->handleCommand($command);
        return $this->createJsonResponse($resourceKind);
    }

    /**
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(ResourceKind $resourceKind) {
        $this->handleCommand(new ResourceKindDeleteCommand($resourceKind));
        return new Response('', 204);
    }
}
