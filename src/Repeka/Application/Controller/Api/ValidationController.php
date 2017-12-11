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
    // TODO add some validation endpoints here...
}
