<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_STATIC_METADATA')")
     */
    public function postAction(Request $request) {
        $command = MetadataCreateCommand::fromArray($request->request->all());
        $metadata = $this->commandBus->handle($command);
        return $this->createJsonResponse($metadata, 201);
    }

    /**
     * @Route
     * @Method("PUT")
     * @Security("has_role('ROLE_STATIC_METADATA')")
     */
    public function updateOrderAction(Request $request) {
        $ids = $request->request->all();
        $command = new MetadataUpdateOrderCommand($ids);
        $this->commandBus->handle($command);
        return $this->createJsonResponse(true);
    }
}
