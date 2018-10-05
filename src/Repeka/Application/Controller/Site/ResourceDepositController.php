<?php
namespace Repeka\Application\Controller\Site;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourceDepositController extends Controller {
    use CommandBusAware;

    public function depositAction(string $template, array $depositConfig, array $headers, Request $request) {
        $phase = $request->get('phase');
        $model = ['phase' => $phase];
        switch ($phase) {
            case "resources":
                $model = $this->resourcePhase($request, $model);
                break;
            case "form":
                $model = $this->formPhase($request, $model);
                break;
            case "tree":
                $relationshipResourceKindIds = $depositConfig['relationship_resource_kind_ids'] ?? [];
                return $this->fetchResourcesToRelationship($request, $relationshipResourceKindIds);
                break;
        }
        $response = $this->render($template, $model);
        $response->headers->add($headers);
        return $response;
    }

    private function resourcePhase(Request $request, $model): array {
        $resourceKindId = $request->get('resourceKindId');
        Assertion::notEmpty($resourceKindId, 'Resource kind id must be present');
        $model['resourceKind'] = $this->getResourceKind($resourceKindId);
        return $model;
    }

    private function fetchResourcesToRelationship(Request $request, array $relationshipResourceKindIds) {
        $resourceKindIds = array_map(function ($id) {
            return intval($id);
        }, $request->get('resourceKinds', []));
        $notAllowedResourceKindIds = array_diff($resourceKindIds, $relationshipResourceKindIds);
        if (!empty($notAllowedResourceKindIds)) {
            return new JsonResponse('', Response::HTTP_FORBIDDEN, [], true);
        }
        return FirewallMiddleware::bypass(function () use ($request) {
            return $this->forward('Repeka\Application\Controller\Api\ResourcesController::getTreeAction', ['request' => $request]);
        });
    }

    private function formPhase(Request $request, $model): array {
        $edit = $request->get('edit');
        if (!$edit) {
            $parentResourceId = $request->get('parentResourceId');
            Assertion::notEmpty($parentResourceId, 'Resource id must be present');
            $parentResource = $this->getResource($parentResourceId);
        }
        $resourceKindId = $request->get('resourceKindId');
        Assertion::notEmpty($resourceKindId, 'Resource kind id must be present');
        $resourceKind = $this->getResourceKind($resourceKindId);
        $model['parentResource'] = $parentResource ?? null;
        $model['resourceKind'] = $resourceKind;
        return $model;
    }

    private function getResource(int $resourceId): ResourceEntity {
        $query = ResourceListQuery::builder()
            ->filterByIds([$resourceId])
            ->build();
        /** @var PageResult $result */
        $result = $this->handleCommand($query);
        return $result->getResults()[0];
    }

    private function getResourceKind(int $resourceKindId) {
        $query = ResourceKindListQuery::builder()
            ->filterByIds([$resourceKindId])
            ->build();
        return $this->handleCommand($query)[0];
    }
}
