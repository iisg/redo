<?php
namespace Repeka\Application\Controller;

use Repeka\Domain\UseCase\Validation\MatchAgainstRegexQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/validation")
 */
class ValidationController extends ApiController {
    /**
     * @Route("/regex")
     * @Method("POST")
     */
    public function regexMatch(Request $request) {
        $query = MatchAgainstRegexQuery::fromArray($request->request->all());
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
