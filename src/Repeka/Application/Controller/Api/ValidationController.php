<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Validation\MatchAgainstRegexQuery;
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
     * @Route("/regex")
     * @Method("POST")
     * @Security("has_role('ROLE_OPERATOR')")
     */
    public function regexMatchAll(Request $request) {
        $query = MatchAgainstRegexQuery::fromArray($request->request->all());
        $result = $this->handleCommand($query);
        return new Response('', $result ? Response::HTTP_NO_CONTENT : Response::HTTP_BAD_REQUEST);
    }
}
