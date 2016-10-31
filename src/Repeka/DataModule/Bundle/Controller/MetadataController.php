<?php
namespace Repeka\DataModule\Bundle\Controller;

use Repeka\CoreModule\Bundle\Controller\ApiController;
use Repeka\DataModule\Bundle\Entity\Metadata;
use Repeka\DataModule\Bundle\Form\MetadataType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * @Route("/metadata")
 */
class MetadataController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     */
    public function getListAction() {
        $metadataList = $this->getDoctrine()->getRepository('DataModuleBundle:Metadata')->findAll();
        return $this->createJsonResponse($metadataList);
    }

    /**
     * @Route
     * @Method("POST")
     */
    public function postAction(Request $request) {
        $metadata = new Metadata();
        $form = $this->createForm(MetadataType::class, $metadata);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($metadata);
            $em->flush();
            return $this->createJsonResponse($metadata, 201);
        } else {
            throw new PreconditionFailedHttpException();
        }
    }
}
