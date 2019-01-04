<?php
namespace Repeka\Application\Controller\Api;

use M6Web\Component\Statsd\Client;
use Repeka\Application\EventListener\CsrfRequestListener;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\User\UserByUserDataQuery;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Domain\UseCase\User\UserQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getListAction() {
        $user = $this->handleCommand(new UserListQuery());
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getUserAction(int $id) {
        $query = new UserQuery($id);
        $user = $this->handleCommand($query);
        return $this->createJsonResponse($user);
    }

    /**
     * @Route("/byData/{resource}")
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function getUserByDataAction(ResourceEntity $resource) {
        $relatedUser = $this->handleCommand(new UserByUserDataQuery($resource));
        return $this->createJsonResponse($relatedUser);
    }
}
