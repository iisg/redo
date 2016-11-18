<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
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
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $command = MetadataCreateCommand::fromArray($request->request->all());
        $metadata = $this->commandBus->handle($command);
        return $this->createJsonResponse($metadata, 201);
    }
}
