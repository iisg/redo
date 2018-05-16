<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildWithBaseCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommand;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataTopLevelPathQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    public function getListAction(Request $request) {
        $queryBuilder = MetadataListQuery::builder();
        if ($resourceClasses = $request->query->get('resourceClasses')) {
            Assertion::isArray($resourceClasses);
            $queryBuilder->filterByResourceClasses($resourceClasses);
        }
        if ($controls = $request->query->get('controls')) {
            Assertion::isArray($controls);
            $controls = array_map(
                function (string $control) {
                    return new MetadataControl($control);
                },
                $controls
            );
            $queryBuilder->filterByControls($controls);
        }
        if ($request->query->get('topLevel')) {
            $queryBuilder->onlyTopLevel();
        }
        if ($parentId = $request->query->get('parentId')) {
            $parent = $this->handleCommand(new MetadataGetQuery($parentId));
            $queryBuilder->filterByParent($parent);
        }
        if ($ids = $request->query->get('ids')) {
            $queryBuilder->filterByIds($ids);
        }
        $metadataList = $this->handleCommand($queryBuilder->build());
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
     * @Route("/{id}/hierarchy")
     * @Method("GET")
     */
    public function getHierarchy(Metadata $metadata) {
        $path = $this->handleCommand(new MetadataTopLevelPathQuery($metadata));
        return $this->createJsonResponse($path);
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
        $resourceClass = $request->get('resourceClass', '');
        $command = new MetadataUpdateOrderCommand($ids, $resourceClass);
        $this->handleCommand($command);
        return $this->createJsonResponse(true);
    }

    /**
     * @Route("/{metadata}")
     * @Method("DELETE")
     */
    public function deleteAction(Metadata $metadata) {
        $this->handleCommand(new MetadataDeleteCommand($metadata));
        return new Response('', 204);
    }
}
