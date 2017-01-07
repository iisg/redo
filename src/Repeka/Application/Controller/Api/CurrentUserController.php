<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Application\Repository\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurrentUserController extends Controller {
    /**
     * @Route("/user/current")
     */
    public function getCurrentUserAction() {
        /** @var User $user */
        $user = $this->getUser();
        $this->get('m6_statsd')->increment('repeka.admin_panel.visit');
        $this->get('repeka.event_listener.csrf_request_listener')->refreshToken();
        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
        ]);
    }
}
