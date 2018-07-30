<?php
namespace Repeka\Application\Controller\Api;

use M6Web\Component\Statsd\Client;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\EventListener\CsrfRequestListener;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\User\UserByUserDataQuery;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Domain\UseCase\User\UserQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->createJsonResponse($user, Response::HTTP_OK, [ResourceNormalizer::DO_NOT_STRIP_RESOURCE_CONTENT]);
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
}
