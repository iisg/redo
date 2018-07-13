<?php
namespace Repeka\Application\Controller;

use Repeka\Domain\UseCase\Assignment\TaskListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/** @Route("/tasks") */
class TasksController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $user = $this->getUser();
        $tasks = $this->handleCommand(new TaskListQuery($user));
        return $this->createJsonResponse($tasks);
    }
}
