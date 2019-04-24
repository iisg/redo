<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Metadata\MetadataConstraintCheckQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/validation")
 */
class ValidationController extends ApiController {
    /**
     * @Route("")
     * @Method("POST")
     * @Security("is_authenticated()")
     */
    public function metadataConstraintCheck(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'constraintName');
        Assertion::keyExists($data, 'metadataId');
        Assertion::keyExists($data, 'kindId');
        Assertion::keyExists($data, 'resourceContents');
        $resourceOrKind = null;
        if ($resourceId = $data['resourceId'] ?? null) {
            $resourceQuery = ResourceListQuery::builder()->filterByIds([$resourceId])->build();
            $resources = $this->handleCommand($resourceQuery);
            if (!count($resources)) {
                throw $this->createNotFoundException();
            }
            $resourceOrKind = $resources[0];
        }
        if (!$resourceOrKind) {
            $resourceOrKind = $this->handleCommand(new ResourceKindQuery($data['kindId']));
            $this->denyAccessUnlessGranted(['VIEW'], $resourceOrKind);
        }
        $query = new MetadataConstraintCheckQuery(
            $data['constraintName'],
            $data['value'] ?? null,
            $data['metadataId'],
            $data['resourceContents'],
            $resourceOrKind
        );
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
