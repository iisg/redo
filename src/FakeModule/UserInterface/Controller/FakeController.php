<?php
declare (strict_types = 1);
namespace Repeka\FakeModule\UserInterface\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Repeka\FakeModule\Application\Notification\ListsAdminEmailAddresses;

/**
 * Class FakeController
 * @package Repeka\FakeModule\UserInterface\Controller
 * @Route(service="fake_controller")
 */
class FakeController {
    /**
     * @var EngineInterface
     */
    private $engine;
    /**
     * @var ListsAdminEmailAddresses
     */
    private $listsAdminEmailAddresses;

    /**
     * FakeController constructor.
     * @param EngineInterface $engine
     * @param ListsAdminEmailAddresses $listsAdminEmailAddresses
     */
    public function __construct(EngineInterface $engine, ListsAdminEmailAddresses $listsAdminEmailAddresses) {
        $this->engine = $engine;
        $this->listsAdminEmailAddresses = $listsAdminEmailAddresses;
    }

    /**
     * @param Request $request
     * @Route("/", name="fake_module_index", methods={"GET"})
     * @return Response
     */
    public function indexAction(Request $request) : Response {
        $view = '@FakeModule/index.html.twig';
        $context = ['listAdminEmailAddress' => $this->listsAdminEmailAddresses->getAdminEmailAddresses()];
        return $this->engine->renderResponse($view, $context);
    }
}
