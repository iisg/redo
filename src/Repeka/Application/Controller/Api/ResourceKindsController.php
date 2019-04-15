<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/resource-kinds")
 */
class ResourceKindsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $resourceClasses = $request->query->get('resourceClasses', []);
        $ids = $request->query->get('ids', []);
        $metadataId = $request->query->get('metadataId', 0);
        $workflowId = $request->query->get('workflowId', 0);
        $sortByIds = $request->query->get('sortByIds', []);
        Assertion::isArray($resourceClasses);
        Assertion::isArray($ids);
        Assertion::isArray($sortByIds);
        $resourceKindListQueryBuilder = ResourceKindListQuery::builder()
            ->filterByResourceClasses($resourceClasses)
            ->filterByMetadataId($metadataId)
            ->filterByWorkflowId($workflowId)
            ->sortBy($sortByIds)
            ->filterByIds($ids);
        $resourceKindListQuery = $resourceKindListQueryBuilder->build();
        $resourceKindList = $this->handleCommand($resourceKindListQuery);
        $resourceKindList = array_values(
            array_filter(
                $resourceKindList,
                function (ResourceKind $resourceKind) {
                    return $this->isGranted(['VIEW'], $resourceKind);
                }
            )
        );
        return $this->createJsonResponse($resourceKindList);
    }

    /**
     * @Route("/{resourceKind}")
     * @Method("GET")
     * @Security("is_granted('VIEW', resourceKind)")
     */
    public function getAction(ResourceKind $resourceKind) {
        return $this->createJsonResponse($resourceKind);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        $command = new ResourceKindCreateCommand(
            $data['name'] ?? '',
            $data['label'] ?? [],
            $data['metadataList'] ?? [],
            $data['allowedToClone'] ?? false,
            $data['workflowId'] ?? null
        );
        $resourceKind = $this->handleCommand($command);
        return $this->createJsonResponse($resourceKind, 201);
    }

    /**
     * @Route("/{id}")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function putAction(ResourceKind $resourceKind, Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'label');
        Assertion::keyExists($data, 'metadataList');
        $command = new ResourceKindUpdateCommand(
            $resourceKind,
            $data['label'],
            $data['metadataList'],
            $data['allowedToClone'] ?? false,
            $data['workflowId'] ?? null
        );
        $resourceKind = $this->handleCommand($command);
        return $this->createJsonResponse($resourceKind);
    }

    /**
     * @Route("/{id}")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function deleteAction(ResourceKind $resourceKind) {
        $this->handleCommand(new ResourceKindDeleteCommand($resourceKind));
        return new Response('', 204);
    }
}
