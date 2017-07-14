<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
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
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $metadataList = $this->handleCommand(new MetadataListQuery());
        return $this->createJsonResponse($metadataList);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(int $id) {
        $metadata = $this->handleCommand(new MetadataGetQuery($id));
        return $this->createJsonResponse($metadata);
    }

    /**
     * @Route("/{parentId}/metadata")
     * @Method("GET")
     */
    public function getAllChildrenListAction(int $parentId) {
        $metadataChildrenList = $this->handleCommand(new MetadataListQuery($parentId));
        return $this->createJsonResponse($metadataChildrenList);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $command = MetadataCreateCommand::fromArray($request->request->all());
        $metadata = $this->handleCommand($command);
        return $this->createJsonResponse($metadata, 201);
    }

    /**
     * @Route("/{parent}/metadata")
     * @Method("POST")
     */
    public function postChildAction(Metadata $parent, Request $request) {
        $data = $request->request->all();
        $baseMetadata = isset($data['baseId']) ? $this->metadataRepository->findOne($data['baseId']) : null;
        $newChildMetadata = $data['newChildMetadata'] ?? [];
        if ($baseMetadata) {
            $command = new MetadataChildWithBaseCreateCommand($parent, $baseMetadata, $newChildMetadata);
        } else {
            $command = new MetadataChildCreateCommand($parent, $newChildMetadata);
        }
        $metadata = $this->handleCommand($command);
        return $this->createJsonResponse($metadata, 201);
    }

    /**
     * @Route("/{id}")
     * @Method("PATCH")
     */
    public function patchAction(int $id, Request $request) {
        $command = MetadataUpdateCommand::fromArray($id, $request->request->all());
        $updatedMetadata = $this->handleCommand($command);
        return $this->createJsonResponse($updatedMetadata);
    }

    /**
     * @Route
     * @Method("PUT")
     */
    public function updateOrderAction(Request $request) {
        $ids = $request->request->all();
        $command = new MetadataUpdateOrderCommand($ids);
        $this->handleCommand($command);
        return $this->createJsonResponse(true);
    }
}
