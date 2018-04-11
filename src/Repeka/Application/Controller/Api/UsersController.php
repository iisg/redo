<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use M6Web\Component\Statsd\Client;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\EventListener\CsrfRequestListener;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\User\UserByUserDataQuery;
use Repeka\Domain\UseCase\User\UserGroupsQuery;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleQuery;
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
     * @Route("/byData/{resource}")
     * @Method("GET")
     */
    public function getUserByDataAction(ResourceEntity $resource) {
        $relatedUser = $this->handleCommand(new UserByUserDataQuery($resource));
        return $this->createJsonResponse($relatedUser);
    }

    /**
     * @Route("/{user}")
     * @Method("PATCH")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateUserAction(UserEntity $user, Request $request) {
        $data = $request->request->all();
        if (isset($data['roleIds'])) {
            Assertion::isArray($data['roleIds']);
            $roles = array_map(
                function ($roleId) {
                    return $this->handleCommand(new UserRoleQuery($roleId));
                },
                $data['roleIds']
            );
            $command = new UserUpdateRolesCommand($user, $roles, $this->getUser());
            $user = $this->handleCommand($command);
        }
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/{user}/groups")
     * @Method("GET")
     */
    public function getUserGroupsAction(UserEntity $user) {
        $groups = $this->handleCommand(new UserGroupsQuery($user));
        return $this->createJsonResponse($groups);
    }
}
