<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleDeleteCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleListQuery;
use Repeka\Domain\UseCase\UserRole\UserRoleUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/user-roles")
 */
class UserRolesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getListAction() {
        $userRole = $this->handleCommand(new UserRoleListQuery());
        return $this->createJsonResponse($userRole);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $request) {
        $name = $request->request->all()['name'] ?? [];
        $userRole = $this->handleCommand(new UserRoleCreateCommand($name));
        return $this->createJsonResponse($userRole, 201);
    }

    /**
     * @Route("/{userRole}")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function putAction(UserRole $userRole, Request $request) {
        $newName = $request->request->all()['name'] ?? [];
        $command = new UserRoleUpdateCommand($userRole, $newName);
        $updatedRole = $this->handleCommand($command);
        return $this->createJsonResponse($updatedRole);
    }

    /**
     * @Route("/{userRole}")
     * @Method("DELETE")
     */
    public function deleteAction(UserRole $userRole) {
        $this->handleCommand(new UserRoleDeleteCommand($userRole));
        return new Response('', 204);
    }
}
