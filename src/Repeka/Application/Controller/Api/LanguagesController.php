<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageDeleteCommand;
use Repeka\Domain\UseCase\Language\LanguageListQuery;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/languages")
 */
class LanguagesController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $language = $this->handleCommand(new LanguageListQuery());
        return $this->createJsonResponse($language);
    }

    /**
     * @Route
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function postAction(Request $request) {
        $command = LanguageCreateCommand::fromArray($request->request->all());
        $language = $this->handleCommand($command);
        return $this->createJsonResponse($language, 201);
    }

    /**
     * @Route("/{code}")
     * @Method("PATCH")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function patchAction(string $code, Request $request) {
        $command = LanguageUpdateCommand::fromArray($code, $request->request->all());
        $updatedLanguage = $this->handleCommand($command);
        return $this->createJsonResponse($updatedLanguage);
    }

    /**
     * @Route("/{code}")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function deleteAction(string $code) {
        $command = new LanguageDeleteCommand($code);
        $this->handleCommand($command);
        return new Response('', 204);
    }
}
