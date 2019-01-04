<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowPluginsQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/workflows")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceWorkflowsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getListAction(Request $request) {
        $resourceClass = $request->query->get('resourceClass', '');
        $command = new ResourceWorkflowListQuery($resourceClass);
        $workflows = $this->handleCommand($command);
        return $this->createJsonResponse($workflows);
    }

    /**
     * @Route("/simulation")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
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
        return $this->createJsonResponse($this->handleCommand($command));
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getAction(string $id) {
        $workflow = $this->handleCommand(new ResourceWorkflowQuery(intval($id)));
        return $this->createJsonResponse($workflow);
    }

    /**
     * @Route("/{workflow}")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function putAction(ResourceWorkflow $workflow, Request $request) {
        $data = $request->request->all();
        $command = new ResourceWorkflowUpdateCommand(
            $workflow,
            $data['name'] ?? [],
            $data['places'] ?? [],
            $data['transitions'] ?? [],
            $data['diagram'] ?? null,
            $data['thumbnail'] ?? null
        );
        $workflow = $this->handleCommand($command);
        return $this->createJsonResponse($workflow);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function postAction(Request $request) {
        $data = $request->request->all();
        $command = new ResourceWorkflowCreateCommand(
            $data['name'] ?? [],
            $data['places'] ?? [],
            $data['transitions'] ?? [],
            $data['resourceClass'] ?? '',
            $data['diagram'] ?? null,
            $data['thumbnail'] ?? null
        );
        $resource = $this->handleCommand($command);
        return $this->createJsonResponse($resource, 201);
    }

    /**
     * @Route("/{workflow}")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function deleteAction(ResourceWorkflow $workflow) {
        $this->handleCommand(new ResourceWorkflowDeleteCommand($workflow));
        return new Response('', 204);
    }

    /**
     * @Route("/{workflow}/plugins")
     * @Method("GET")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getWorkflowPluginsAction(ResourceWorkflow $workflow) {
        /** @var ResourceWorkflowPlugin[] $plugins */
        $plugins = $this->handleCommand(new ResourceWorkflowPluginsQuery($workflow));
        return $this->createJsonResponse($plugins, 200);
    }
}
