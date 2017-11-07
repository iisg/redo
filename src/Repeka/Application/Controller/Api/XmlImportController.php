<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Domain\UseCase\XmlImport\XmlImportQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/xmlImport")
 */
class XmlImportController extends ApiController {
    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction(string $id) {
        $xml = $this->handleCommand(new XmlImportQuery($id));
        return $this->createXmlResponse($xml);
    }
}
