<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Domain\Constants\SystemRole;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller {
    /**
     * @Route("/admin{suffix}", requirements={"suffix"=".*"}, methods={"GET"})
     * @Template
     */
    public function adminAction($suffix = null) {
        if ($suffix && preg_match('#\..{2,4}$#', $suffix)) {
            throw new NotFoundHttpException("$suffix file could not be found");
        } elseif (!$this->getUser()) {
            $this->redirectToRoute('login');
        } elseif (!$this->getUser()->hasRole(SystemRole::OPERATOR()->roleName())) {
            throw $this->createAccessDeniedException();
        }
    }

    /** @Route("/", methods={"GET"}, name="home") */
    public function homeAction() {
        return $this->render($this->getParameter('repeka.templates.homepage'));
    }

    /** @Route("/login", name="login") */
    public function loginAction() {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse('/admin');
        }
        $authenticationUtils = $this->get('security.authentication_utils');
        return $this->render(
            $this->getParameter('repeka.templates.login_form'),
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }
}
