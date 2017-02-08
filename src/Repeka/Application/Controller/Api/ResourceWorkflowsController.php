<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/workflows")
 */
class ResourceWorkflowsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $workflows = $this->handle(new ResourceWorkflowListQuery());
        return $this->createJsonResponse($workflows);
    }

    /**
     * @Route("/simulation")
     * @Method("POST")
     */
    public function simulateAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'workflow');
        $command = new ResourceWorkflowSimulateCommand(
            $data['workflow']['places'] ?? [],
            $data['workflow']['transitions'] ?? [],
            $data['fromState'] ?? [],
            $data['transition'] ?? ''
        );
        return $this->createJsonResponse($this->handle($command));
    }

    /**
     * @Route("/{workflow}")
     * @Method("GET")
     */
    public function getAction(int $workflow) {
        $workflow = $this->handle(new ResourceWorkflowQuery($workflow));
        return $this->createJsonResponse($workflow);
    }

    /**
     * @Route("/{workflow}")
     * @Method("PUT")
     */
    public function putAction(ResourceWorkflow $workflow, Request $request) {
        $data = $request->request->all();
        $command = new ResourceWorkflowUpdateCommand(
            $workflow,
            $data['places'] ?? [],
            $data['transitions'] ?? [],
            $data['diagram'] ?? null,
            $data['thumbnail'] ?? null
        );
        $workflow = $this->handle($command);
        return $this->createJsonResponse($workflow);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        $command = new ResourceWorkflowCreateCommand($data['name'] ?? []);
        $resource = $this->handle($command);
        return $this->createJsonResponse($resource, 201);
    }
}
