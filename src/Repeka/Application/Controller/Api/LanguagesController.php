<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageListQuery;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("has_role('ROLE_STATIC_LANGUAGES')")
     */
    public function postAction(Request $request) {
        $command = LanguageCreateCommand::fromArray($request->request->all());
        $language = $this->commandBus->handle($command);
        return $this->createJsonResponse($language, 201);
    }

    /**
     * @Route("/{code}")
     * @Method("PATCH")
     * @Security("has_role('ROLE_STATIC_LANGUAGES')")
     */
    public function patchAction(string $code, Request $request) {
        $command = LanguageUpdateCommand::fromArray($code, $request->request->all());
        $updatedLanguage = $this->commandBus->handle($command);
        return $this->createJsonResponse($updatedLanguage);
    }
}
