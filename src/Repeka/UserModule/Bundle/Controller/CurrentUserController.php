<?php
namespace Repeka\UserModule\Bundle\Controller;

use Repeka\UserModule\Bundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurrentUserController extends Controller {
    /**
     * @Route("/api/user/current")
     */
    public function getCurrentUserAction() {
        /** @var User $user */
        $user = $this->getUser();
        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail()
        ]);
    }
}
