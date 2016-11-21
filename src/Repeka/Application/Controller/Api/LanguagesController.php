<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/languages")
 */
class LanguagesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $language = $this->commandBus->handle(new LanguageListQuery());
        return $this->createJsonResponse($language);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $command = LanguageCreateCommand::fromArray($request->request->all());
        $language = $this->commandBus->handle($command);
        return $this->createJsonResponse($language, 201);
    }
}
