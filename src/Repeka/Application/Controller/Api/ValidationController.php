<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Metadata\MetadataConstraintCheckQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
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
        Assertion::keyExists($data, 'resourceId');
        Assertion::keyExists($data, 'resourceContents');
        $resourceQuery = ResourceListQuery::builder()->filterByIds([$data['resourceId']])->build();
        $resources = $this->handleCommand($resourceQuery);
        if (!count($resources)) {
            throw $this->createNotFoundException();
        }
        $query = new MetadataConstraintCheckQuery(
            $data['constraintName'],
            $data['value'] ?? null,
            $data['metadataId'],
            $resources[0],
            $data['resourceContents']
        );
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
