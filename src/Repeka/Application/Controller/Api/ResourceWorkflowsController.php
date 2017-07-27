<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUsingMetadataAsAssigneeQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/workflows")
 */
class ResourceWorkflowsController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     * @ParamConverter("assigneeMetadata", converter="Repeka\Application\ParamConverter\AssigneeMetadataParamConverter")
     */
    public function getListAction(?Metadata $assigneeMetadata, Request $request) {
        $resourceClass = $request->query->get('resourceClass', '');
        $command = ($assigneeMetadata == null)
            ? new ResourceWorkflowListQuery($resourceClass)
            : new ResourceWorkflowUsingMetadataAsAssigneeQuery($assigneeMetadata);
        $workflows = $this->handleCommand($command);
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
        return $this->createJsonResponse($this->handleCommand($command));
    }

    /**
     * @Route("/{workflow}")
     * @Method("GET")
     */
    public function getAction(int $workflow) {
        $workflow = $this->handleCommand(new ResourceWorkflowQuery($workflow));
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
            $data['name'] ?? [],
            $data['places'] ?? [],
            $data['transitions'] ?? [],
            $data['diagram'] ?? null,
            $data['thumbnail'] ?? null,
            $data['resourceClass'] ?? ''
        );
        $workflow = $this->handleCommand($command);
        return $this->createJsonResponse($workflow);
    }

    /**
     * @Route
     * @Method("POST")
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
     */
    public function deleteAction(ResourceWorkflow $workflow) {
        $this->handleCommand(new ResourceWorkflowDeleteCommand($workflow));
        return new Response('', 204);
    }
}
