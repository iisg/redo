<?php

namespace Repeka\Application\Controller\Api;

use M6Web\Component\Statsd\Client;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\EventListener\CsrfRequestListener;
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
    /** @var CsrfRequestListener */
    private $csrfRequestListener;
    /** @var Client */
    private $statsd;

    public function __construct(CsrfRequestListener $csrfRequestListener, Client $statsd) {
        $this->csrfRequestListener = $csrfRequestListener;
        $this->statsd = $statsd;
    }

    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $user = $this->handleCommand(new UserListQuery());
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/current")
     * @Method("GET")
     */
    public function getCurrentUserAction() {
        /** @var UserEntity $user */
        $user = $this->getUser();
        $this->statsd->increment('repeka.admin_panel.visit');
        $this->csrfRequestListener->refreshToken();
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getUserAction(int $id) {
        $query = new UserQuery($id);
        $user = $this->handleCommand($query);
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
            $user = $this->handleCommand($command);
        }
        return $this->createJsonResponse($user);
    }
}
