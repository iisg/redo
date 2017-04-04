<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/users")
 */
class UsersController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $user = $this->commandBus->handle(new UserListQuery());
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/current")
     * @Method("GET")
     */
    public function getCurrentUserAction() {
        /** @var UserEntity $user */
        $user = $this->getUser();
        $this->get('m6_statsd')->increment('repeka.admin_panel.visit');
        $this->get('repeka.event_listener.csrf_request_listener')->refreshToken();
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getUserAction(int $id) {
        $query = new UserQuery($id);
        $user = $this->commandBus->handle($query);
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/{user}")
     * @Method("PATCH")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateUserAction(UserEntity $user, Request $request) {
        $data = $request->request->all();
        if (isset($data['roleIds'])) {
            $roleIds = $data['roleIds'];
            $roleRepository = $this->getDoctrine()->getRepository('RepekaDomain:UserRole');
            $roles = array_map(function ($roleId) use ($roleRepository) {
                return $roleRepository->findOne($roleId);
            }, $roleIds);
            $command = new UserUpdateRolesCommand($user, $roles);
            $user = $this->commandBus->handle($command);
        }
        return $this->createJsonResponse($user);
    }
}
