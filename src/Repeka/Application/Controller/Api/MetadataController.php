<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildWithBaseCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/metadata")
 */
class MetadataController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $metadataList = $this->commandBus->handle(new MetadataListQuery());
        return $this->createJsonResponse($metadataList);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(int $id) {
        $metadata = $this->handle(new MetadataGetQuery($id));
        return $this->createJsonResponse($metadata);
    }

    /**
     * @Route("/{parentId}/metadata")
     * @Method("GET")
     */
    public function getAllChildrenListAction(int $parentId) {
        $metadataChildrenList = $this->commandBus->handle(new MetadataListQuery($parentId));
        return $this->createJsonResponse($metadataChildrenList);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $command = MetadataCreateCommand::fromArray($request->request->all());
        $metadata = $this->commandBus->handle($command);
        return $this->createJsonResponse($metadata, 201);
    }

    /**
     * @Route("/{parent}/metadata")
     * @Method("POST")
     */
    public function postChildAction(Metadata $parent, Request $request) {
        $data = $request->request->all();
        $baseMetadata = isset($data['baseId']) ? $this->get('repository.metadata')->findOne($data['baseId']) : null;
        $newChildMetadata = $data['newChildMetadata'] ?? [];
        if ($baseMetadata) {
            $command = new MetadataChildWithBaseCreateCommand($parent, $baseMetadata);
        } else {
            $command = new MetadataChildCreateCommand($parent, $newChildMetadata);
        }
        $metadata = $this->commandBus->handle($command);
        return $this->createJsonResponse($metadata, 201);
    }

    /**
     * @Route("/{id}")
     * @Method("PATCH")
     */
    public function patchAction(int $id, Request $request) {
        $command = MetadataUpdateCommand::fromArray($id, $request->request->all());
        $updatedMetadata = $this->commandBus->handle($command);
        return $this->createJsonResponse($updatedMetadata);
    }

    /**
     * @Route
     * @Method("PUT")
     */
    public function updateOrderAction(Request $request) {
        $ids = $request->request->all();
        $command = new MetadataUpdateOrderCommand($ids);
        $this->commandBus->handle($command);
        return $this->createJsonResponse(true);
    }
}
