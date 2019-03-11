<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Validation\CheckUniquenessQuery;
use Repeka\Domain\UseCase\Validation\MatchAgainstRegexQuery;
use Repeka\Domain\UseCase\Validation\ValidatePeselQuery;
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
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function regexMatch(Request $request) {
        $query = MatchAgainstRegexQuery::fromArray($request->request->all());
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/uniqueInResourceClass")
     * @Method("POST")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function uniqueInResourceClass(Request $request) {
        $query = CheckUniquenessQuery::fromArray($request->request->all());
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/validPesel")
     * @Method("POST")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function isValidPesel(Request $request) {
        $query = new ValidatePeselQuery($request->request->all()['metadataValue'] ?? '');
        $this->handleCommand($query);
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
