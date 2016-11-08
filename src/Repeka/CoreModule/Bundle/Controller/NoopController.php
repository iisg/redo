<?php
namespace Repeka\CoreModule\Bundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to ping the session and to push frontend metrics if no action has been taken by the user.
 */
class NoopController extends Controller {
    /**
     * @Route("/api/noop")
     */
    public function noopAction() {
        return new Response();
    }
}
