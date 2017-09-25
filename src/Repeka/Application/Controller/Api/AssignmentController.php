<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Assignment\AssignmentListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/** @Route("/assignment") */
class AssignmentController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $user = $this->getUser();
        $assignments = $this->handleCommand(new AssignmentListQuery($user));
        return $this->createJsonResponse($assignments);
    }
}
